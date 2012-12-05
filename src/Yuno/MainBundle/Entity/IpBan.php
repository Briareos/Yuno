<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IpBan
 */
class IpBan
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
     * @var string
     */
    private $ipv4;

    /**
     * @var string
     */
    private $ipv6;

    /**
     * @var integer
     */
    private $subnet;


    private function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @return IpBan
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
     * Set ipv4
     *
     * @param string $ipv4
     * @return IpBan
     */
    public function setIpv4($ipv4)
    {
        $this->ipv4 = $ipv4;

        return $this;
    }

    /**
     * Get ipv4
     *
     * @return string
     */
    public function getIpv4()
    {
        return $this->ipv4;
    }

    /**
     * Set ipv6
     *
     * @param string $ipv6
     * @return IpBan
     */
    public function setIpv6($ipv6)
    {
        $this->ipv6 = $ipv6;

        return $this;
    }

    /**
     * Get ipv6
     *
     * @return string
     */
    public function getIpv6()
    {
        return $this->ipv6;
    }

    /**
     * Set subnet
     *
     * @param integer $subnet
     * @return IpBan
     */
    public function setSubnet($subnet)
    {
        $this->subnet = $subnet;

        return $this;
    }

    /**
     * Get subnet
     *
     * @return integer
     */
    public function getSubnet()
    {
        return $this->subnet;
    }

    public function isOnlyOneIpPresent()
    {
        return empty($this->ipv4) ^ empty($this->ipv6);
    }
}
