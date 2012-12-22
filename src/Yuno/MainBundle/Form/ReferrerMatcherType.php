<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

class ReferrerMatcherType extends AbstractType
{
    public function getName()
    {
        return 'time_range';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            array(
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ),
                'choices' => array(
                    'starts' => "Start with",
                    'regex' => "Match regex",
                ),
                'attr' => array(
                    'class' => '',
                ),
                'label_render' => false,
                'widget_controls' => false,
                'widget_control_group' => false,
            )
        );
        $builder->add(
            'pattern',
            'text',
            array(
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ),
                'widget_addon' => array(
                    'type' => 'append',
                    'icon' => 'filter'
                ),
                'attr' => array(
                    'class' => '',
                ),
                'label_render' => false,
                'widget_controls' => false,
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
                $constraints[] = new \Symfony\Component\Validator\Constraints\NotBlank();
                if ($data['type'] === 'regex') {
                    $constraints[] = new \Yuno\MainBundle\Validator\Constraints\IsRegex();
                }
                $form->remove('pattern');
                $form->add(
                    $factory->createNamed(
                        'pattern',
                        'text',
                        null,
                        array(
                            'constraints' => $constraints,
                            'widget_addon' => array(
                                'type' => 'append',
                                'icon' => 'filter'
                            ),
                            'attr' => array(
                                'class' => '',
                            ),
                            'widget_controls' => false,
                            'widget_control_group' => false,
                        )
                    )
                );
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'prototype' => false,
            )
        );
    }


}