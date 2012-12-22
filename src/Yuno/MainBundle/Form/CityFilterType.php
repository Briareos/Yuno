<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            array(
                'label' => "Country",
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ),
                'widget_prefix' => "Country",
                'attr' => array(
                    'class' => 'chosen',
                    'data-placeholder' => "Country",
                ),
                'required' => true,
                'label_render' => false,
                'widget_control_group' => false,
            )
        );
        $builder->add(
            'city',
            'text',
            array(
                'required' => true,
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ),
                'widget_prefix' => "City",
                'label_render' => false,
                'widget_control_group' => false,
            )
        );
        $builder->add(
            'region',
            'choice',
            array(
                'choices' => Campaign::getAvailableRegions(),
                'required' => false,
                'label' => "State/region list (only for United States and Canada)",
                'attr' => array(
                    'class' => 'chosen',
                    'data-placeholder' => "State/region",
                ),
                'widget_prefix' => "State/region (US/CA only)",
                'label_render' => false,
                'widget_control_group' => false,
            )
        );

        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::PRE_BIND,
            function (FormEvent $event) use ($factory) {
                $data = $event->getData();
                if (empty($data)) {
                    return;
                }
                $form = $event->getForm();
                $constraints = array();
                if ($data['country'] === 'US' || $data['country'] === 'CA') {
                    $constraints[] = new \Symfony\Component\Validator\Constraints\NotBlank();
                }
                $form->remove('region');
                $form->add(
                    $factory->createNamed(
                        'region',
                        'choice',
                        null,
                        array(
                            'constraints' => $constraints,
                            'choices' => Campaign::getAvailableRegions(),
                            'required' => false,
                            'label' => "State/region list (only for United States and Canada)",
                            'attr' => array(
                                'class' => 'chosen',
                                'data-placeholder' => "State/region",
                            ),
                            'widget_prefix' => "State/region (US/CA only)",
                            'widget_control_group' => false,
                        )
                    )
                );
            }
        );
    }

}