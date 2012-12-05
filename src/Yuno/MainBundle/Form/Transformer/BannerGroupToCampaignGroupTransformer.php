<?php

namespace Yuno\MainBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;

class BannerGroupToCampaignGroupTransformer implements DataTransformerInterface
{
    private $em;

    function __construct(Entitymanager $em)
    {
        $this->em = $em;
    }


    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if($value === null){
            return null;
        }
        if(is_object($value)){

        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if($value === null) {
            return null;
        }
        if(is_object($value)){

        }
    }

}