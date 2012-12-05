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
                $dispersionForm = $factory->createNamed('clickDispersion', 'form');
                foreach ($banners as $banner) {
                    $dispersionForm->add(
                        $factory->createNamed(
                            $banner->getId(),
                            'text',
                            null,
                            array(
                                //'disabled' => !$banner->getSite()->getActive()
                            )
                        )
                    );
                }
                $event->getForm()->add($dispersionForm);
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Yuno\MainBundle\Entity\CampaignGroup',
                'data_class' => 'Yuno\MainBundle\Entity\CampaignGroup',
            )
        );
    }


}