<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CampaignGroup
 */
class CampaignGroup
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $clickLimit;

    /**
     * @var array
     */
    private $clickDispersion;

    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var BannerGroup
     */
    private $bannerGroup;

    public function __construct()
    {
        $this->createdAt       = new \DateTime();
        $this->clickDispersion = [];
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return CampaignGroup
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set clickLimit
     *
     * @param integer $clickLimit
     *
     * @return CampaignGroup
     */
    public function setClickLimit($clickLimit)
    {
        $this->clickLimit = $clickLimit;

        return $this;
    }

    /**
     * Get clickLimit
     *
     * @return integer
     */
    public function getClickLimit()
    {
        return $this->clickLimit;
    }

    /**
     * Set clickDispersion
     *
     * @param array $clickDispersion
     *
     * @return CampaignGroup
     */
    public function setClickDispersion(array $clickDispersion)
    {
        $this->clickDispersion = $clickDispersion;

        return $this;
    }

    /**
     * Get clickDispersion
     *
     * @return array
     */
    public function getClickDispersion()
    {
        if (array_diff_key($this->clickDispersion, (array) $this->getBanners()->toArray())
            || array_diff_key((array) $this->getBanners()->toArray(), $this->clickDispersion)
        ) {
            $this->clickDispersion = [];
            $randomNumbers         = $this->generateRandomNumbers($this->getBanners()->count(), 100);
            $i                     = 0;
            foreach ($this->getBanners() as $banner) {
                $this->clickDispersion[$banner->getId()] = $randomNumbers[$i++];
            }
        }

        return $this->clickDispersion;
    }

    /**
     *
     * @link http://stackoverflow.com/a/7289357/232947
     *
     * @param $count
     * @param $sum
     *
     * @return array
     */
    public static function generateRandomNumbers($count, $sum)
    {
        if (empty($count)) {
            return [];
        }
        $groups = [];
        $group  = 0;
        while (array_sum($groups) != $sum) {
            $groups[$group] = mt_rand(0, $sum / mt_rand(1, 5));

            if (++$group == $count) {
                $group = 0;
            }
        }

        return $groups;
    }

    /**
     * @return \Yuno\MainBundle\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param \Yuno\MainBundle\Entity\Campaign $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return \Yuno\MainBundle\Entity\BannerGroup
     */
    public function getBannerGroup()
    {
        return $this->bannerGroup;
    }

    /**
     * @param \Yuno\MainBundle\Entity\BannerGroup $bannerGroup
     */
    public function setBannerGroup($bannerGroup)
    {
        $this->bannerGroup = $bannerGroup;
    }

    public function getRandomBannerId(array $clickDispersion)
    {
        $clickDispersion = $this->getClickDispersion();
        $random          = rand(1, array_sum($clickDispersion));
        $max             = array_sum($clickDispersion);
        foreach ($clickDispersion as $bannerId => $bannerValue) {
            $max -= $bannerValue;
            if ($random > $max) {
                return $bannerId;
            }
        }
    }

    /**
     * @return ArrayCollection|Banner[]
     */
    public function getBanners()
    {
        $banners = new ArrayCollection();
        foreach ($this->bannerGroup->getBanners() as $banner) {
            if ($banner->getSite()->getUser()->getId() === $this->campaign->getUser()->getId()) {
                $banners{$banner->getId()} = $banner;
            }
        }

        return $banners;
    }

    /**
     * @return Banner
     */
    public function chooseAndGetBanner()
    {
        $banners         = $this->getBanners();
        $clickDispersion = $this->getClickDispersion();
        foreach ($banners as $banner) {
            if (!$banner->getSite()->getActive()) {
                unset($clickDispersion[$banner->getId()]);
            }
        }
        $bannerId     = $this->getRandomBannerId($clickDispersion);
        $chosenBanner = $banners->get($bannerId);

        return $chosenBanner;
    }
}
