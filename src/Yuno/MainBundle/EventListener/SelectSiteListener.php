<?php

namespace Yuno\MainBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class SelectSiteListener
{
    private $securityContext;

    private $controllerResolver;

    private $em;

    function __construct(SecurityContextInterface $securityContext, ControllerResolverInterface $controllerResolver, EntityManager $em)
    {
        $this->securityContext = $securityContext;
        $this->controllerResolver = $controllerResolver;
        $this->em = $em;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();

        if ($user === null) {
            return;
        }

        if ($user->getSelectedSite() !== null) {
            if (!$this->securityContext->isGranted('EDIT', $user->getSelectedSite())) {
                $user->deselectSite();
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        $controller = $event->getController();
        if (!$controller[0] instanceof \Yuno\MainBundle\Controller\BannerController
          && !$controller[0] instanceof \Yuno\MainBundle\Controller\ClickController
        ) {
            return;
        }
        if ($user->getSelectedSite() === null) {
            $event->getRequest()->attributes->set('_controller', 'MainBundle:Site:selectWarning');
            $event->setController($this->controllerResolver->getController($event->getRequest()));
        }
    }

    public function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}