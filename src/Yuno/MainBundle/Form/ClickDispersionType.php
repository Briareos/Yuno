<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ClickDispersionType extends AbstractType
{
    public function getName()
    {
        return 'click_dispersion';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dispersion', 'text');
        var_dump(__FILE__);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }


}