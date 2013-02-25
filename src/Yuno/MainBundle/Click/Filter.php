<?php

namespace Yuno\MainBundle\Click;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Yuno\MainBundle\Entity\CampaignGroup;

class Filter
{
    const BLOCK_BANNED_CITY = 1;
    const BLOCK_BANNED_REGION = 2;
    const BLOCK_BANNED_COUNTRY = 3;
    const BLOCK_BANNED_IP = 4;
    const BLOCK_DUPLICATE_IP = 5;
    const BLOCK_CAMPAIGN_GROUP_QUOTA_REACHED = 6;
    const BLOCK_REQUEST_URI_SUSPICIOUS = 7;
    const BLOCK_HAS_COOKIES = 8;
    const BLOCK_INVALID_REFERRER = 9;
    const BLOCK_BANNED_USER_AGENT = 10;
    const BLOCK_PROXY_DETECTED = 11;
    const BLOCK_LOCATION_NOT_RECOGNIZED = 12;
    const BLOCK_GROUP_NOT_ACTIVE = 13;
    const BLOCK_CAMPAIGN_NOT_ACTIVE = 14;
    const BLOCK_OUT_OF_SCHEDULE = 15;
    const BLOCK_NO_ACTIVE_SITES = 16;
    const PASS = null;

    private static $statuses = array(
        self::BLOCK_BANNED_CITY => 'banned city',
        self::BLOCK_BANNED_REGION => 'banned region',
        self::BLOCK_BANNED_COUNTRY => 'banned country',
        // @TODO implement self::BLOCK_BANNED_IP => 'banned ip',
        self::BLOCK_DUPLICATE_IP => 'duplicate ip',
        self::BLOCK_CAMPAIGN_GROUP_QUOTA_REACHED => 'quota reached',
        // @TODO implement self::BLOCK_REQUEST_URI_SUSPICIOUS => 'request uri suspicious',
        self::BLOCK_HAS_COOKIES => 'has cookies',
        self::BLOCK_INVALID_REFERRER => 'invalid referrer',
        self::BLOCK_BANNED_USER_AGENT => 'banned user agent',
        // @TODO implement self::BLOCK_PROXY_DETECTED => 'proxy detected',
        self::BLOCK_LOCATION_NOT_RECOGNIZED => 'unknown location',
        // @TODO implement self::BLOCK_GROUP_NOT_ACTIVE => 'group not active',
        self::BLOCK_CAMPAIGN_NOT_ACTIVE => 'campaign inactive',
        self::BLOCK_OUT_OF_SCHEDULE => 'out of schedule',
        self::BLOCK_NO_ACTIVE_SITES => 'no active sites',
        self::PASS => 'passed',
    );

    private $request;

    private $campaignGroup;

    private $log = array();

    private $em;

    private $time;

    public static function getStatuses()
    {
        return static::$statuses;
    }

    public function __construct(Request $request, CampaignGroup $campaignGroup, EntityManager $em, $time = 'now')
    {
        $this->request = $request;
        $this->campaignGroup = $campaignGroup;
        $this->em = $em;
        $this->time = $time;
    }

    private function addLog($log)
    {
        $this->log[] = $log;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getStatus()
    {
        if ($this->hasCookies()) {
            return static::BLOCK_HAS_COOKIES;
        }

        if ($this->isLocationUnrecognized()) {
            return static::BLOCK_LOCATION_NOT_RECOGNIZED;
        }

        if ($this->isCampaignInactive()) {
            return static::BLOCK_CAMPAIGN_NOT_ACTIVE;
        }

        if ($this->isUserAgentBanned()) {
            return static::BLOCK_BANNED_USER_AGENT;
        }

        if ($this->isCountryBanned()) {
            return static::BLOCK_BANNED_COUNTRY;
        }

        if ($this->isRegionBanned()) {
            return static::BLOCK_BANNED_REGION;
        }

        if ($this->isCityBanned()) {
            return static::BLOCK_BANNED_CITY;
        }

        if ($this->isInvalidReferrer()) {
            return static::BLOCK_INVALID_REFERRER;
        }

        if ($this->isDuplicateIp()) {
            return static::BLOCK_DUPLICATE_IP;
        }

        if ($this->isCampaignGroupQuotaReached()) {
            return static::BLOCK_CAMPAIGN_GROUP_QUOTA_REACHED;
        }

        if ($this->isOutOfSchedule()) {
            return static::BLOCK_OUT_OF_SCHEDULE;
        }

        if ($this->noActiveSites()) {
            return static::BLOCK_NO_ACTIVE_SITES;
        }

        return static::PASS;
    }

    private function hasCookies()
    {
        if ($this->request->cookies->count()) {
            $this->addLog(sprintf('Cookie check: %s cookies found, "%s".', $this->request->cookies->count(), implode('", "', array_keys($this->request->cookies->all()))));

            return true;
        }
        $this->addLog('Cookie check: No cookies found.');

        return false;
    }

    private function isCampaignInactive()
    {
        if ($this->campaignGroup->getCampaign()->getActive()) {
            $this->addLog(sprintf('Campaign activity check: Campaign "%s" is active.', $this->campaignGroup->getCampaign()->getName()));

            return false;
        }
        $this->addLog(sprintf('Campaign activity check: Campaign "%s" is not active.', $this->campaignGroup->getCampaign()->getName()));

        return true;
    }

    private function isLocationUnrecognized()
    {
        if (
            $this->request->server->get('GEOIP_CONTINENT_CODE')
            && $this->request->server->get('GEOIP_COUNTRY_CODE')
            && $this->request->server->get('GEOIP_CITY')
        ) {
            $this->addLog('Location check: Location info recognized.');

            return false;
        }
        if (!$this->request->server->get('GEOIP_CONTINENT_CODE')) {
            $unrecognized = 'continent';
        } elseif (!$this->request->server->get('GEOIP_COUNTRY_CODE')) {
            $unrecognized = 'country';
        } else {
            $unrecognized = 'city';
        }
        $this->addLog(sprintf('Location check: Unrecognized location info, unknown %s.', $unrecognized));

        return true;
    }

    private function isCountryBanned()
    {
        $whitelistedCountries = $this->campaignGroup->getCampaign()->getCountryList();
        if (empty($whitelistedCountries)) {
            $this->addLog('Country check: There are no whitelisted countries, pass.');

            return false;
        }
        $country = $this->request->server->get('GEOIP_COUNTRY_CODE');
        if (in_array($country, $whitelistedCountries)) {
            $this->addLog(sprintf('Country check: "%s" found among whitelisted countries.', $country));

            return false;
        }
        $this->addLog(sprintf('Country check: Country "%s" is not whitelisted.', $country));

        return true;
    }

    private function isRegionBanned()
    {
        $whitelistedRegions = $this->campaignGroup->getCampaign()->getRegionList();
        if (empty($whitelistedRegions)) {
            $this->addLog('State/region check: There are no whitelisted states/regions.');

            return false;
        }
        $region = $this->request->server->get('GEOIP_REGION');
        if (in_array($region, $whitelistedRegions)) {
            $this->addLog(sprintf('State/region check: "%s" found among whitelisted states/regions.', $region));

            return false;
        }
        $this->addLog(sprintf('State/region check: State/region "%s" is not whitelisted.', $region));

        return true;
    }

    private function isCityBanned()
    {
        $blacklistedCities = $this->campaignGroup->getCampaign()->getCityList();
        if (empty($blacklistedCities)) {
            $this->addLog('City check: There are no blacklisted cities, pass.');

            return false;
        }

        $city = $this->request->server->get('GEOIP_CITY');
        $region = $this->request->server->get('GEOIP_REGION');
        $country = $this->request->server->get('GEOIP_COUNTRY_CODE');

        foreach ($blacklistedCities as $blacklistedCity) {
            if ($blacklistedCity['country'] === $country
                && $blacklistedCity['city'] === $city
                && (($blacklistedCity['region'] !== 'US' && $blacklistedCity['region'] !== 'CA') || $blacklistedCity['region'] === $region)
            ) {
                $this->addLog(sprintf('City check: City "%s", (%s), "%s" is blacklisted.', $city, $region, $country));

                return true;
            }
        }
        $this->addLog(sprintf('City check: City "%s", (%s), "%s" is not blacklisted.', $city, $region, $country));

        return false;
    }

    private function isDuplicateIp()
    {
        $days = 21;
        $duplicate = $this->em->createQuery('Select c.id From MainBundle:Click c Where c.createdAt > :date And c.ip = :ip')
            ->setMaxResults(1)
            ->setParameter('date', new \DateTime(sprintf('-%s days', $days)))
            ->setParameter('ip', $this->request->server->get('REMOTE_ADDR'))
            ->getOneOrNullResult();
        if ($duplicate) {
            $this->addLog(sprintf('Duplicate IP check: IP address has been logged in the last %s days.', $days));

            return true;
        }
        $this->addLog(sprintf('Duplicate IP check: IP address has not been logged in the last %s days.', $days));

        return false;
    }

    private function isCampaignGroupQuotaReached()
    {
        $count = $this->em->createQuery('Select Count(c.id) From MainBundle:Click c Inner Join c.banner b Where c.createdAt > :date And c.blocked Is Null And b.group = :group')
            ->setParameter('date', $this->campaignGroup->getCampaign()->getPreviousMidnight())
            ->setParameter('group', $this->campaignGroup->getBannerGroup())
            ->getSingleScalarResult();

        if ($count >= $this->campaignGroup->getClickLimit()) {
            $this->addLog(sprintf('Campaign group quota status: Campaign group quota already reached at %s clicks.', $this->campaignGroup->getClickLimit()));

            return true;
        }
        $this->addLog(sprintf('Campaign group quota status: Campaign group quota not reached, currently at %s/%s.', $count, $this->campaignGroup->getClickLimit()));

        return false;
    }

    private function isInvalidReferrer()
    {
        if (!$this->request->server->get('HTTP_REFERER')) {
            if ($this->campaignGroup->getCampaign()->getAllowEmptyReferrer()) {
                $this->addLog('Referrer check: Empty referrers are allowed.');

                return false;
            }
            $this->addLog('Referrer check: Empty referrers are not allowed.');

            return true;
        }
        $whitelistedReferrers = $this->campaignGroup->getCampaign()->getReferrerList();
        if (empty($whitelistedReferrers)) {
            $this->addLog('Referrer check: There are no whitelisted referrers, pass.');

            return false;
        }
        foreach ($whitelistedReferrers as $whitelistedReferrer) {
            $referrer = $this->request->server->get('HTTP_REFERER');
            if ($whitelistedReferrer['type'] === 'regex') {
                if (preg_match($whitelistedReferrer['pattern'], $referrer)) {
                    $this->addLog(sprintf('Referrer check: Referrer matches a regex pattern: "%s".', $whitelistedReferrer['pattern']));

                    return false;
                }
            } elseif ($whitelistedReferrer['type'] === 'starts') {
                if (strpos($referrer, $whitelistedReferrer['pattern']) === 0) {
                    $this->addLog(sprintf('Referrer check: Referrer starts with a set pattern: "%s".', $whitelistedReferrer['pattern']));

                    return false;
                }
            }
        }
        $this->addLog('Referrer check: Referrer is not whitelisted.');

        return true;
    }

    private function isOutOfSchedule()
    {
        $schedules = $this->campaignGroup->getCampaign()->getSchedule();
        if (empty($schedules)) {
            $this->addLog('Schedule check: There are no scheduled intervals.');

            return false;
        }
        $timezone = new \DateTimeZone($this->campaignGroup->getCampaign()->getTimezone());
        foreach ($schedules as $schedule) {
            $startTime = new \DateTime($schedule['startTime'], $timezone);
            $endTime = new \DateTime($schedule['endTime'], $timezone);
            $currentTime = new \DateTime($this->time, $timezone);
            if ($currentTime >= $startTime && $currentTime <= $endTime) {
                $this->addLog(sprintf('Schedule check: Scheduled interval found, from %s to %s, timezone: %s.', $startTime->format('H:i:s'), $endTime->format('H:i:s'), $timezone->getName()));

                return false;
            }
        }
        $this->addLog('Schedule check: Out of the range of scheduled intervals.');

        return true;
    }

    private function noActiveSites()
    {
        if ($this->campaignGroup->getBanners()->count()) {
            $this->addLog(sprintf('Banner checker: %s active site banner found.', $this->campaignGroup->getBanners()->count()));

            return false;
        }
        $this->addLog('Banner checker: No active site banners found.');

        return true;
    }

    private function isUserAgentBanned()
    {
        $userAgent = $this->request->server->get('HTTP_USER_AGENT');
        $bannedUserAgents = array(
            'msnbot',
            'bingbot',
            'crawler',
            'slurp',
            'yahoo',
        );
        foreach ($bannedUserAgents as $bannedUserAgent) {
            if (stripos($userAgent, $bannedUserAgent) !== false) {
                $this->addLog(sprintf('User agent check: user agent is banned, matches filter "%s".', htmlentities($bannedUserAgent, ENT_QUOTES, 'UTF-8')));

                return true;
            }
        }
        $this->addLog('User agent check: user agent is not banned.');

        return false;
    }
}
