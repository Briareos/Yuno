<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Yuno\MainBundle\Entity\Campaign;
use Yuno\MainBundle\Entity\CampaignGroupRepository;
use Doctrine\ORM\EntityManager;
use Yuno\MainBundle\Form\Transformer\BannerGroupToCampaignGroupTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Yuno\MainBundle\Entity\UserRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampaignType extends AbstractType
{

    private $securityContext;

    private $em;

    function __construct(SecurityContextInterface $securityContext, EntityManager $em)
    {
        $this->securityContext = $securityContext;
        $this->em              = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add(
            'allowEmptyReferrer',
            'checkbox',
            [
                'label'    => "Allow empty referrer",
                'required' => false,
            ]
        );
        $builder->add(
            'active',
            null,
            [
                'label'    => "Active",
                'required' => false,
            ]
        );
        $builder->add('timezone', 'timezone');
        $builder->add(
            'countryList',
            'country',
            [
                'multiple' => true,
                'required' => false,
                'label'    => "Country list",
                'attr'     => [
                    'data-placeholder' => "Select countries",
                ],
            ]
        );
        $builder->add(
            'regionList',
            'choice',
            [
                'choices'  => Campaign::getAvailableRegions(),
                'multiple' => true,
                'required' => false,
                'label'    => "State/region list",
                'attr'     => [
                    'data-placeholder' => "Select states/regions",
                ],
            ]
        );
        $builder->add(
            'cityList',
            'collection',
            [
                'label'          => "Blacklisted cities",
                'type'           => new CityFilterType(),
                'allow_add'      => true,
                'allow_delete'   => true,
                'widget_add_btn' => ['label' => "Add", 'attr' => ['class' => 'btn']],
                'show_legend'    => false, // dont show another legend of subform
                'by_reference'   => false,
                'options'        => [ // options for collection fields
                    'label_render'         => false,
                    'widget_control_group' => false,
                    'widget_remove_btn'    => ['label' => "Remove", 'attr' => ['class' => 'btn']],
                    'attr'                 => ['class' => 'input-large'],
                ],
            ]
        );
        $builder->add(
            'schedule',
            'collection',
            [
                'type'           => new TimeRangeType(),
                'allow_add'      => true,
                'allow_delete'   => true,
                'widget_add_btn' => ['label' => "Add", 'attr' => ['class' => 'btn']],
                'show_legend'    => false, // dont show another legend of subform
                'by_reference'   => false,
                'options'        => [ // options for collection fields
                    'label_render'         => false,
                    'widget_control_group' => false,
                    'widget_remove_btn'    => ['label' => "Remove", 'attr' => ['class' => 'btn']],
                    'attr'                 => ['class' => 'input-large'],
                ],
            ]
        );
        $builder->add(
            'referrerList',
            'collection',
            [
                'label'          => "Whitelisted referrers",
                'type'           => new ReferrerMatcherType(),
                'allow_add'      => true,
                'allow_delete'   => true,
                'widget_add_btn' => ['label' => "Add", 'attr' => ['class' => 'btn']],
                'show_legend'    => false,
                'by_reference'   => false,
                'options'        => [
                    'label_render'         => false,
                    'widget_control_group' => false,
                    'widget_remove_btn'    => ['label' => "Remove", 'attr' => ['class' => 'btn']],
                    'attr'                 => ['class' => 'input-xlarge'],
                ]
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                /** @var $campaign \Yuno\MainBundle\Entity\Campaign */
                $campaign = $event->getData();
                $data     = null;
                $form     = $event->getForm();
                $options  = [
                    'class' => 'MainBundle:User',
                ];
                if ($campaign->getUser() === null) {
                    $data = $this->securityContext->getToken()->getUser();
                }
                if (!$this->securityContext->isGranted('ROLE_SITE_EDIT_ALL')) {
                    $options['read_only']     = true;
                    $options['query_builder'] = function (UserRepository $er) {
                        $qb = $er->createQueryBuilder('u');
                        $qb->where('u = :user');
                        $qb->setParameter('user', $this->securityContext->getToken()->getUser());

                        return $qb;
                    };
                }
                if ($campaign->getId()) {
                    $options['disabled'] = true;
                }
                $options['data'] = $data;
                $form->add('user', 'entity', $options);
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Yuno\MainBundle\Entity\Campaign'
            ]
        );
    }

    public function getName()
    {
        return 'campaign';
    }
}
