<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;

class CampaignGroupsType extends AbstractType
{
    public function getName()
    {
        return 'campaign_groups';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'campaignGroups',
            'collection',
            array(
                'type' => new CampaignGroupType(),
                'by_reference' => false,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Yuno\MainBundle\Entity\Campaign',
            )
        );
    }


}