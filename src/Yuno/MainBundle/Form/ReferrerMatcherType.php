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
            [
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'choices'              => [
                    'starts' => "Start with",
                    'regex'  => "Match regex",
                ],
                'attr'                 => [
                    'class' => '',
                ],
                'label_render'         => false,
                'widget_controls'      => false,
                'widget_control_group' => false,
            ]
        );
        $builder->add(
            'pattern',
            'text',
            [
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'widget_addon'         => [
                    'type' => 'append',
                    'icon' => 'filter'
                ],
                'attr'                 => [
                    'class' => '',
                ],
                'label_render'         => false,
                'widget_controls'      => false,
                'widget_control_group' => false,
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (empty($data)) {
                return;
            }
            $form          = $event->getForm();
            $constraints   = [];
            $constraints[] = new \Symfony\Component\Validator\Constraints\NotBlank();
            if ($data['type'] === 'regex') {
                $constraints[] = new \Yuno\MainBundle\Validator\Constraints\IsRegex();
            }
            $form->remove('pattern');
            $form->add('pattern', 'text', [
                'constraints'          => $constraints,
                'widget_addon'         => [
                    'type' => 'append',
                    'icon' => 'filter'
                ],
                'attr'                 => [
                    'class' => '',
                ],
                'widget_controls'      => false,
                'widget_control_group' => false,
            ]);
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'prototype' => false,
            ]
        );
    }
}