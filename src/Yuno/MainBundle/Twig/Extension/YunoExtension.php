<?php

namespace Yuno\MainBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;

class YunoExtension extends \Twig_Extension
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yuno';
    }

    public function getFunctions()
    {
        return array(

        );
    }


}