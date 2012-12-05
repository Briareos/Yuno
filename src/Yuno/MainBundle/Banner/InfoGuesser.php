<?php

namespace Yuno\MainBundle\Banner;

class InfoGuesser
{
    private $code;

    function __construct($code)
    {
        $this->code = $code;
    }

    public function guessHumanUrl()
    {
        $url = null;
        preg_match('{href\s?=\s?([\'"])(.*?)\1}', $this->code, $resultUrl);
        if (!empty($resultUrl[2])) {
            $url = $resultUrl[2];
        }

        return $url;
    }

    public function guessBotUrl()
    {
        $ch = curl_init($this->guessHumanUrl());
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
            )
        );
        $response = curl_exec($ch);
        $lastUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        preg_match('{<meta[^>]*http-equiv=([\'"])refresh\1[^>]*content=([\'"])\d+;[^>]*url=[\'"]?([^>]*)[\'"]?\2[^>]*>}ism', $response, $resultMetaRefresh);

        if (!empty($resultMetaRefresh[3])) {
            preg_match('{^http.*?(http.*?)$}', $resultMetaRefresh[3], $resultLastUrl);
            if (!empty($resultLastUrl[1])) {
                $lastUrl = $resultLastUrl[1];
            } else {
                $lastUrl = $resultMetaRefresh[3];
            }
        }

        return strtolower('http://' . parse_url($lastUrl, PHP_URL_HOST));
    }

    public function guessSize()
    {
        $size = array();

        preg_match('{src=([\'""])(.*?)\1}i', $this->code, $resultImageSrc);
        if (!empty($resultImageSrc[2])) {
            $imageSrc = $resultImageSrc[2];
            $imageSize = getimagesize($imageSrc);
            if (!empty($imageSize[0]) && !empty($imageSize[1])) {
                $size = array($imageSize[0], $imageSize[1]);
            }
        }

        return $size;
    }
}