<?php

namespace Yuno\MainBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class PageListener
{

    private $pages;

    function __construct($pages)
    {
        $this->pages = rtrim($pages, '/');
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $path = $event->getRequest()->getPathInfo();
        if ($path === '/') {
            $path = '/home.php';
        }
        if (!preg_match('{^/[a-z0-9_-]+\.(?:php|html|css|js)$}i', $path)
            && !preg_match('{^/(?:images|img)/[a-z0-9_/-]+\.(?:jpg|jpeg|png|gif)$}i', $path)
            && !preg_match('{^/(?:style|css)/[a-z0-9_-]+\.css$}i', $path)
            && !preg_match('{^/js/[a-z0-9_\.-]+\.js$}i', $path)
            && !preg_match('{^/[a-z0-9]+\.xml$}i', $path)
            && !preg_match('{^/fonts?/[a-z0-9_\.-]+\.(woff|ttf|svg|eot)$}i', $path)
            && !(preg_match('{^/favicon\.(ico|png)$}', $path, $matches) && file_exists($this->pages.$matches[0]))
        ) {
            return;
        }
        $file = $this->pages.'/'.$path;
        if (!file_exists($file)) {
            return;
        }
        $contentType = 'text/html';
        $extension   = pathinfo($file, PATHINFO_EXTENSION);
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
        } elseif ($extension === 'xml') {
            $contentType = 'application/xml';
        } elseif ($extension === 'ico') {
            $contentType = 'image/x-icon';
        } elseif ($extension === 'svg') {
            $contentType = 'image/svg+xml';
        } elseif ($extension === 'woff') {
            $contentType = 'application/font-woff';
        } elseif ($extension === 'ttf') {
            $contentType = 'application/x-font-ttf';
        } elseif ($extension === 'eot') {
            $contentType = 'application/vnd.ms-fontobject';
        } elseif ($extension === 'otf') {
            $contentType = 'application/x-font-opentype';
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
        $response = new Response($responseContent, 200, [
            'Content-Type' => $contentType,
        ]);
        $event->setResponse($response);
    }
}
