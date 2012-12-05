<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Campaign
 */
class   Campaign
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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var Click[]
     */
    private $clicks;

    /**
     * @var CampaignGroup[]
     */
    private $campaignGroups;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $schedule;

    /**
     * @var array
     */
    private $countryList;

    /**
     * @var string
     */
    private $timezone;

    /**
     * @var array
     */
    private $referrerList;

    /**
     * @var array
     */
    private $cityList;

    /**
     * @var array
     */
    private $regionList;

    /**
     * @var boolean
     */
    private $allowEmptyReferrer;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->clicks = new ArrayCollection();
        $this->campaignGroups = new ArrayCollection();
        $this->schedule = array();
        $this->countryList = array();
        $this->referrerList = array();
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
     * @return Campaign
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Campaign
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
     * Set active
     *
     * @param boolean $active
     * @return Campaign
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    public function getClicks()
    {
        return $this->clicks;
    }

    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
    }

    public function getCampaignGroups()
    {
        return $this->campaignGroups;
    }

    public function setCampaignGroups($campaignGroups)
    {
        $this->campaignGroups = $campaignGroups;
    }

    /**
     * @return \Yuno\MainBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Yuno\MainBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param array $schedule
     */
    public function setSchedule(array $schedule)
    {
        $this->schedule = array_values($schedule);
    }

    /**
     * @return array
     */
    public function getCountryList()
    {
        return $this->countryList;
    }

    /**
     * @param array $countryList
     */
    public function setCountryList($countryList)
    {
        $this->countryList = $countryList;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function getPreviousMidnight()
    {
        $midnight = new \DateTime('now', new \DateTimeZone($this->getTimezone()));
        $midnight->modify('midnight');

        return $midnight;
    }

    public function getNextMidnight()
    {
        $midnight = new \DateTime('now', new \DateTimeZone($this->getTimezone()));
        $midnight->modify('midnight + 1 day');

        return $midnight;
    }

    /**
     * @return array
     */
    public function getReferrerList()
    {
        return $this->referrerList;
    }

    /**
     * @param array $referrerList
     */
    public function setReferrerList(array $referrerList)
    {
        $this->referrerList = array_values($referrerList);
    }

    /**
     * @return array
     */
    public function getCityList()
    {
        return $this->cityList;
    }

    /**
     * @param array $cityList
     */
    public function setCityList(array $cityList)
    {
        $this->cityList = array_values($cityList);
    }

    /**
     * @return array
     */
    public function getRegionList()
    {
        return $this->regionList;
    }

    /**
     * @param array $regionList
     */
    public function setRegionList(array $regionList)
    {
        $this->regionList = array_values($regionList);
    }

    public static function getAvailableRegions()
    {
        return array(
            "United States" => array(
                "AA" => "Armed Forces Americas",
                "AE" => "Armed Forces Europe, Middle East, & Canada",
                "AK" => "Alaska",
                "AL" => "Alabama",
                "AP" => "Armed Forces Pacific",
                "AR" => "Arkansas",
                "AS" => "American Samoa",
                "AZ" => "Arizona",
                "CA" => "California",
                "CO" => "Colorado",
                "CT" => "Connecticut",
                "DC" => "District of Columbia",
                "DE" => "Delaware",
                "FL" => "Florida",
                "FM" => "Federated States of Micronesia",
                "GA" => "Georgia",
                "GU" => "Guam",
                "HI" => "Hawaii",
                "IA" => "Iowa",
                "ID" => "Idaho",
                "IL" => "Illinois",
                "IN" => "Indiana",
                "KS" => "Kansas",
                "KY" => "Kentucky",
                "LA" => "Louisiana",
                "MA" => "Massachusetts",
                "MD" => "Maryland",
                "ME" => "Maine",
                "MH" => "Marshall Islands",
                "MI" => "Michigan",
                "MN" => "Minnesota",
                "MO" => "Missouri",
                "MP" => "Northern Mariana Islands",
                "MS" => "Mississippi",
                "MT" => "Montana",
                "NC" => "North Carolina",
                "ND" => "North Dakota",
                "NE" => "Nebraska",
                "NH" => "New Hampshire",
                "NJ" => "New Jersey",
                "NM" => "New Mexico",
                "NV" => "Nevada",
                "NY" => "New York",
                "OH" => "Ohio",
                "OK" => "Oklahoma",
                "OR" => "Oregon",
                "PA" => "Pennsylvania",
                "PR" => "Puerto Rico",
                "PW" => "Palau",
                "RI" => "Rhode Island",
                "SC" => "South Carolina",
                "SD" => "South Dakota",
                "TN" => "Tennessee",
                "TX" => "Texas",
                "UT" => "Utah",
                "VA" => "Virginia",
                "VI" => "Virgin Islands",
                "VT" => "Vermont",
                "WA" => "Washington",
                "WI" => "Wisconsin",
                "WV" => "West Virginia",
                "WY" => "Wyoming",
            ),
            "Canada" => array(
                "AB" => "Alberta",
                "BC" => "British Columbia",
                "MB" => "Manitoba",
                "NB" => "New Brunswick",
                "NL" => "Newfoundland",
                "NS" => "Nova Scotia",
                "NT" => "Northwest Territories",
                "NU" => "Nunavut",
                "ON" => "Ontario",
                "PE" => "Prince Edward Island",
                "QC" => "Quebec",
                "SK" => "Saskatchewan",
                "YT" => "Yukon Territory",
            ),
        );
    }

    /**
     * @return boolean
     */
    public function getAllowEmptyReferrer()
    {
        return $this->allowEmptyReferrer;
    }

    /**
     * @param boolean $allowEmptyReferrer
     */
    public function setAllowEmptyReferrer($allowEmptyReferrer)
    {
        $this->allowEmptyReferrer = $allowEmptyReferrer;
    }
}
