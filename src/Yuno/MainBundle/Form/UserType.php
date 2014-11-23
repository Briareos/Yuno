<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                null,
                [
                    'attr' => [
                        'autocomplete' => "off",
                    ]
                ]
            )
            ->add(
                'plainPassword',
                'repeated',
                [
                    'type'     => 'password',
                    'required' => false,
                ]
            )
            ->add('email')
            ->add('locale', 'locale')
            ->add(
                'timezone',
                'timezone',
                []
            )
            ->add('roles', 'roles', [
                'expanded' => true,
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Yuno\MainBundle\Entity\User'
            ]
        );
    }

    public function getName()
    {
        return 'user';
    }
}
