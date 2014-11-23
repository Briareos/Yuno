<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Yuno\MainBundle\Entity\SiteRepository;
use Yuno\MainBundle\Entity\Banner;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BannerType extends AbstractType
{

    private $securityContext;

    function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'textarea', [
                    'attr' => [
                        'rows' => 4,
                    ]
                ]
            )
            ->add('size', 'choice', [
                    'choices' => Banner::getAvailableSizes(),
                ]
            )
            ->add('humanUrl', 'textarea', [
                    'attr' => [
                        'rows' => 2,
                    ]
                ]
            )
            ->add('botUrl');

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                /** @var $banner Banner */
                $banner = $event->getData();
                $form   = $event->getForm();
                if ($banner->getSite() === null) {
                    $data = $this->securityContext->getToken()->getUser()->getSelectedSite();
                } else {
                    $data = $banner->getSite();
                }
                $form->add('site', 'entity', [
                    'data'          => $data,
                    'class'         => 'MainBundle:Site',
                    'read_only'     => true,
                    'query_builder' => function (SiteRepository $er) use ($data) {
                        $qb = $er->createQueryBuilder('s');
                        $qb->where('s = :site');
                        $qb->setParameter('site', $data);

                        return $qb;
                    }
                ]);
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                /** @var $banner Banner */
                $banner = $event->getData();
                if ($banner->getSite() === null) {
                    $site = $this->securityContext->getToken()->getUser()->getSelectedSite();
                } else {
                    $site = $banner->getSite();
                }
                $data = $banner->getCategory();
                if ($data === null) {
                    $data = -1;
                }
                $choices = [
                    -1 => "Site-wide",
                ];
                if ($site->getCategories()) {
                    $choices["Categories"] = $site->getCategories();
                }
                $form = $event->getForm();
                $form->add('category', 'choice', [
                    'data'        => $data,
                    'choices'     => $choices,
                    'required'    => false,
                    'empty_value' => 'Pages',
                ]);
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Yuno\MainBundle\Entity\Banner'
        ]);
    }

    public function getName()
    {
        return 'banner';
    }
}
