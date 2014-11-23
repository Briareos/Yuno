<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BannerGroup
 */
class BannerGroup
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Banner[]
     */
    private $banners;

    /**
     * @var CampaignGroup[]
     */
    private $campaignGroups;

    public function __construct()
    {
        $this->banners        = new ArrayCollection();
        $this->campaignGroups = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BannerGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getBanners()
    {
        return $this->banners;
    }

    public function setBanners($banners)
    {
        $this->banners = $banners;
    }

    public function __toString()
    {
        return $this->name;
    }
}
