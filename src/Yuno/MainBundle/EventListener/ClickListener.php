<?php

namespace Yuno\MainBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Yuno\MainBundle\Entity\Click;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Yuno\MainBundle\Click\Filter;
use Yuno\MainBundle\Util\Encoder;
use Doctrine\ORM\EntityManager;

class ClickListener
{

    private $em;

    private $encoder;

    function __construct(EntityManager $em, Encoder $encoder)
    {
        $this->em      = $em;
        $this->encoder = $encoder;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->isMethod('get')) {
            return;
        }

        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $queryString = $request->getQueryString();

        if (empty($queryString)) {
            return;
        }

        if (strlen($queryString) % 2) {
            return;
        }

        if (!preg_match('{^[a-f0-9]+$}', $queryString)) {
            return;
        }

        $decoded = $this->encoder->decrypt($queryString);

        if (!is_numeric($decoded)) {
            return;
        }

        $decoded = (int) $decoded;

        /** @var $campaignGroup \Yuno\MainBundle\Entity\CampaignGroup */
        $campaignGroup = $this->em->find('MainBundle:CampaignGroup', $decoded);

        if ($campaignGroup === null) {
            return;
        }

        $filter      = new Filter($request, $campaignGroup, $this->em);
        $clickStatus = $filter->getStatus($request, $campaignGroup);
        $log         = $filter->getLog();

        $click = new Click();
        $click->setCampaign($campaignGroup->getCampaign());

        $click->setBlocked($clickStatus);
        $click->setLog($log);

        $click->setIp($request->server->get('REMOTE_ADDR'));

        if ($request->server->get('HTTP_USER_AGENT')) {
            $click->setUserAgent($request->server->get('HTTP_USER_AGENT'));
        }

        if ($request->server->get('HTTP_REFERER')) {
            $click->setReferrer($request->server->get('HTTP_REFERER'));
        }

        if ($request->server->get('GEOIP_CONTINENT_CODE')) {
            $click->setContinent($request->server->get('GEOIP_CONTINENT_CODE'));
        }
        if ($request->server->get('GEOIP_COUNTRY_CODE')) {
            $click->setCountry($request->server->get('GEOIP_COUNTRY_CODE'));
        }
        if ($request->server->get('GEOIP_REGION')) {
            $click->setRegion($request->server->get('GEOIP_REGION'));
        }
        if ($request->server->get('GEOIP_CITY')) {
            $click->setCity($request->server->get('GEOIP_CITY'));
        }
        if ($request->server->get('GEOIP_LATITUDE')) {
            $click->setLatitude($request->server->get('GEOIP_LATITUDE'));
        }
        if ($request->server->get('GEOIP_LONGITUDE')) {
            $click->setLongitude($request->server->get('GEOIP_LONGITUDE'));
        }

        $blacklisted = false;
        foreach (['msn', 'yahoo', 'bing'] as $blacklist) {
            if ($request->server->get('HTTP_USER_AGENT') && stripos($request->server->get('HTTP_USER_AGENT'), $blacklist)) {
                $blacklisted = true;
                break;
            }
        }

        if ($blacklisted) {
            $click->setBlocked(Filter::BLOCK_BANNED_USER_AGENT);
            $log   = $click->getLog();
            $log[] = 'Possibly Bing/MSN/Yahoo bot; showing a basic maintenance page.';
            $click->setLog($log);
            $this->em->persist($click);
            $this->em->flush();

            return;
        }

        $banner = $campaignGroup->chooseAndGetBanner();

        if ($banner === null) {
            $url = 'http://'.$campaignGroup->getBannerGroup()->getName();
        } else {
            $click->setBanner($banner);
            if ($clickStatus === Filter::PASS) {
                $siteUrl = $banner->getSite()->getUrl();
                $url     = rtrim($siteUrl, '/').'/?'.$this->encoder->encrypt(
                        implode(
                            '#',
                            array(
                                'yuno',
                                time(),
                                $click->getIp(),
                                $banner->getHumanUrl(),
                                $banner->getId(),
                                0,
                            )
                        )
                    );
            } else {
                $url = $banner->getBotUrl();
            }
        }

        $this->em->persist($click);
        $this->em->flush();

        $response = new Response(<<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Under Maintenance</title>
        <meta http-equiv="refresh" content="0; URL=$url">
    </head>
<body>
</body>
</html>
HTML
        );
        $event->setResponse($response);
    }
}
