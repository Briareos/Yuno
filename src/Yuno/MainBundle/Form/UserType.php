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
            array(
                'attr' => array(
                    'autocomplete' => "off",
                )
            )
        )
          ->add(
            'plainPassword',
            'repeated',
            array(
                'type' => 'password',
                'required' => false,
            )
        )
          ->add('email')
          ->add('locale', 'locale')
          ->add(
            'timezone',
            'timezone',
            array()
        )
          ->add('roles', 'roles',array(
                'expanded'=>true,
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Yuno\MainBundle\Entity\User'
            )
        );
    }

    public function getName()
    {
        return 'user';
    }
}
