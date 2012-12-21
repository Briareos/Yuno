<?php

namespace Yuno\MainBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class PageListener
{
    private $pages;


    function __construct($pages)
    {
        $this->pages = $pages;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $uri = $event->getRequest()->getRequestUri();
        if ($uri === '/') {
            $uri = '/home.php';
        }
        if (!preg_match('{^/[a-z0-9_-]+\.(?:php|css|js)$}', $uri) && !preg_match('{^/images/[a-z0-9_-]+\.(?:jpg|jpeg|png|gif)$}', $uri)) {
            return;
        }
        $file = rtrim($this->pages, '/') . $uri;
        if (!file_exists($file)) {
            return;
        }
        $contentType = 'text/html';
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension === 'css') {
            $contentType = 'text/css';
        } elseif ($extension === 'js') {
            $contentType = 'text/javascript';
        } elseif ($extension === 'jpg' || $extension === 'jpeg') {
            $contentType = 'image/jpeg';
        } elseif ($extension === 'png') {
            $contentType = 'image/png';
        } elseif ($extension === 'gif') {
            $contentType = 'image/gif';
        }
        if ($extension === 'php') {
            $cwd = getcwd();
            chdir($this->pages);
            ob_start();
            include $file;
            $responseContent = ob_get_contents();
            ob_end_clean();
            chdir($cwd);
        } else {
            $responseContent = file_get_contents($file);
        }
        $response = new Response($responseContent, 200, array(
            'Content-Type' => $contentType,
        ));
        $event->setResponse($response);
    }
}