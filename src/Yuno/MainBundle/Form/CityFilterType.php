<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Yuno\MainBundle\Entity\Campaign;
use Symfony\Component\Form\FormBuilderInterface;

class CityFilterType extends AbstractType
{
    public function getName()
    {
        return 'time_range';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'country',
            'country',
            [
                'label'                => "Country",
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'widget_prefix'        => "Country",
                'attr'                 => [
                    'class'            => 'chosen',
                    'data-placeholder' => "Country",
                ],
                'required'             => true,
                'label_render'         => false,
                'widget_control_group' => false,
            ]
        );
        $builder->add(
            'city',
            'text',
            [
                'required'             => true,
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'widget_prefix'        => "City",
                'label_render'         => false,
                'widget_control_group' => false,
            ]
        );
        $builder->add(
            'region',
            'choice',
            [
                'choices'              => Campaign::getAvailableRegions(),
                'required'             => false,
                'label'                => "State/region list (only for United States and Canada)",
                'attr'                 => [
                    'class'            => 'chosen',
                    'data-placeholder' => "State/region",
                ],
                'widget_prefix'        => "State/region (US/CA only)",
                'label_render'         => false,
                'widget_control_group' => false,
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (empty($data)) {
                return;
            }
            $form        = $event->getForm();
            $constraints = [];
            if ($data['country'] === 'US' || $data['country'] === 'CA') {
                $constraints[] = new NotBlank();
            }
            $form->remove('region');
            $form->add('region', 'choice', [
                'constraints'          => $constraints,
                'choices'              => Campaign::getAvailableRegions(),
                'required'             => false,
                'label'                => "State/region list (only for United States and Canada)",
                'attr'                 => [
                    'class'            => 'chosen',
                    'data-placeholder' => "State/region",
                ],
                'widget_prefix'        => "State/region (US/CA only)",
                'widget_control_group' => false,
            ]);
        });
    }
}