<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

class TimeRangeType extends AbstractType
{
    public function getName()
    {
        return 'time_range';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'startTime',
            'text',
            [
                //'empty_data' => '08:00:00',
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\Time(),
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'widget_prefix'        => "From",
                'widget_addon'         => [
                    'type' => 'append',
                    'icon' => 'time'
                ],
                'attr'                 => [
                    'class' => 'timepicker input-mini',
                ],
                'label_render'         => false,
                'widget_controls'      => false,
                'widget_control_group' => false,
            ]
        );
        $builder->add(
            'endTime',
            'text',
            [
                //'empty_data' => '16:00:00',
                'constraints'          => [
                    new \Symfony\Component\Validator\Constraints\Time(),
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'widget_prefix'        => "to",
                'widget_addon'         => [
                    'type' => 'append',
                    'icon' => 'time'
                ],
                'attr'                 => [
                    'class' => 'timepicker input-mini',
                ],
                'label_render'         => false,
                'widget_controls'      => false,
                'widget_control_group' => false,
            ]
        );
    }
}