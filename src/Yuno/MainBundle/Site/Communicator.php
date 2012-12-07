<?php

namespace Yuno\MainBundle\Site;

use Yuno\MainBundle\Entity\Site;

class Communicator
{
    private $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    public function getCategories()
    {
        $ch = curl_init($this->site->getUrl());
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(
                    array(
                        'yuno_secret' => $this->site->getSecret(),
                        'action' => 'get_categories',
                    )
                ),
                CURLOPT_RETURNTRANSFER => true,
            )
        );
        $response = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($contentType !== 'application/json') {
            return null;
        }
        // Remove BOM.
        if (substr($response, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $response = substr($response, 3);
        }
        $categories = @json_decode($response, true);
        if (empty($categories)) {
            return null;
        }

        return $categories;
    }

    public function setBanners($banners)
    {
        $ch = curl_init($this->site->getUrl());
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(
                    array(
                        'yuno_secret' => $this->site->getSecret(),
                        'action' => 'set_banners',
                        'banners' => serialize($banners),
                    )
                ),
                CURLOPT_RETURNTRANSFER => true,
            )
        );
        $response = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($contentType !== 'application/json') {
            return false;
        }
        // Remove BOM.
        if (substr($response, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $response = substr($response, 3);
        }
        $responseData = @json_decode($response, true);
        if (empty($responseData['status'])) {
            return false;
        }

        return ($responseData['status'] === "OK");
    }
}