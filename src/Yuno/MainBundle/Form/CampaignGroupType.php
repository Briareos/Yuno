<?php

namespace Yuno\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Yuno\MainBundle\Form\ClickDispersionType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;

class CampaignGroupType extends AbstractType
{
    public function getName()
    {
        return 'campaign_group';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('clickLimit', 'number');

        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($factory) {
                /** @var $campaignGroup \Yuno\MainBundle\Entity\CampaignGroup */
                $campaignGroup = $event->getData();
                if ($campaignGroup === null) {
                    return;
                }
                $banners = $campaignGroup->getBanners();
                $form    = $event->getForm();
                $form->add('clickDispersion', 'form');
                $clickDispersion = $form->get('clickDispersion');
                foreach ($banners as $banner) {
                    $clickDispersion->add($banner->getId(), 'text');
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'class'      => 'Yuno\MainBundle\Entity\CampaignGroup',
                'data_class' => 'Yuno\MainBundle\Entity\CampaignGroup',
            ]
        );
    }
}