<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Yuno\MainBundle\Entity\UserRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteType extends AbstractType
{
    private $securityContext;


    function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name')
          ->add('url', 'url')
          ->add(
            'active',
            null,
            array(
                'required' => false,
            )
        )
          ->add('secret');

        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($factory) {
                /** @var $site \Yuno\MainBundle\Entity\Site */
                $site = $event->getData();
                $data = null;
                $form = $event->getForm();
                $options = array(
                    'class' => 'MainBundle:User',
                );
                if ($site->getUser() === null) {
                    $data = $this->securityContext->getToken()->getUser();

                }
                if (!$this->securityContext->isGranted('ROLE_SITE_EDIT_ALL')) {
                    $options['read_only'] = true;
                    $options['query_builder'] = function (UserRepository $er) {
                        $qb = $er->createQueryBuilder('u');
                        $qb->where('u = :user');
                        $qb->setParameter('user', $this->securityContext->getToken()->getUser());

                        return $qb;
                    };
                }
                $form->add(
                    $factory->createNamed(
                        'user',
                        'entity',
                        $data,
                        $options
                    )
                );
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Yuno\MainBundle\Entity\Site'
            )
        );
    }

    public function getName()
    {
        return 'site';
    }
}
