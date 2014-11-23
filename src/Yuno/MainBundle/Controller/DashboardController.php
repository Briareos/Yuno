<?php

namespace Yuno\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Yuno\MainBundle\Paginator\PaginatorHelper;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{

    /**
     * @var \Doctrine\ORM\EntityManager
     *
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @Route("/", name="dashboard")
     * @Template("MainBundle:Dashboard:dashboard.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function dashboardAction()
    {
        return [];
    }
}