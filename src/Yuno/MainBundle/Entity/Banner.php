<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Banner
 */
class Banner
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $humanUrl;

    /**
     * @var string
     */
    private $botUrl;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var BannerGroup
     */
    private $group;

    /**
     * @var Click[]
     */
    private $clicks;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->clicks = new ArrayCollection();
    }

    public static function getAvailableSizes()
    {
        $sizes = array(
            'text',
            '88x31',
            '100x100',
            '120x60',
            '120x90',
            '120x240',
            '125x125',
            '180x150',
            '200x200',
            '234x60',
            '250x250',
            '300x250',
            '468x60',
            '728x90',
        );

        return array_combine($sizes, $sizes);
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
     * Set size
     *
     * @param string $size
     * @return Banner
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return Banner
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Banner
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set humanUrl
     *
     * @param string $humanUrl
     * @return Banner
     */
    public function setHumanUrl($humanUrl)
    {
        $this->humanUrl = $humanUrl;

        return $this;
    }

    /**
     * Get humanUrl
     *
     * @return string
     */
    public function getHumanUrl()
    {
        return $this->humanUrl;
    }

    /**
     * Set botUrl
     *
     * @param string $botUrl
     * @return Banner
     */
    public function setBotUrl($botUrl)
    {
        $this->botUrl = $botUrl;

        return $this;
    }

    /**
     * Get botUrl
     *
     * @return string
     */
    public function getBotUrl()
    {
        return $this->botUrl;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Banner
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
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     */
    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    /**
     * @return \Yuno\MainBundle\Entity\BannerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param \Yuno\MainBundle\Entity\BannerGroup $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function getClicks()
    {
        return $this->clicks;
    }

    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
    }
}
