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
        if (!preg_match('{^/[a-z0-9]+\.php$}', $uri)) {
            return;
        }
        $file = rtrim($this->pages, '/') . $uri;
        $cwd = getcwd();
        chdir($this->pages);
        ob_start();
        include $file;
        $responseHtml = ob_get_contents();
        ob_end_clean();
        chdir($cwd);
        $response = new Response($responseHtml);
        $event->setResponse($response);
    }
}