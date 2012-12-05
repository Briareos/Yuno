<?php

namespace Yuno\MainBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Yuno\MainBundle\Paginator\PaginatorHelper;
use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\Query;
use Yuno\MainBundle\Entity\User;
use Yuno\MainBundle\Entity\Site;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Yuno\MainBundle\Entity\Click;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Click controller.
 *
 * @Route("/click")
 */
class ClickController extends Controller
{
    /**
     * @var \Doctrine\ORM\EntityManager
     *
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     *
     * @DI\Inject("security.context")
     */
    private $securityContext;

    /**
     * Lists clicks on the selected site.
     *
     * @Route("/", name="click")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_CLICK_LIST_OWN")
     */
    public function indexAction()
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();
        $site = $user->getSelectedSite();

        $clicksQuery = $this->em->getRepository('MainBundle:Click')->createNamedQuery('findAllBySite');
        $clicksQuery->setParameter('site', $site);

        return $this->listClicks($clicksQuery, null, null, $this->getRequest()->query->getInt('page', 1));
    }

    /**
     * Lists clicks on a specified site.
     *
     * @Route("/site/{id}", name="click_site")
     * @Method("GET")
     * @Template("MainBundle:Click:index.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @Secure(roles="ROLE_CLICK_LIST_ALL")
     */
    public function siteAction(Request $request, Site $site)
    {
        $clicksQuery = $this->em->getRepository('MainBundle:Click')->createNamedQuery('findAllBySite');
        $clicksQuery->setParameter('site', $site);

        return $this->listClicks($clicksQuery, null, $site, $request->query->getInt('page', 1));
    }

    /**
     * Lists clicks on owned sites.
     *
     * @Route("/own", name="click_own")
     * @Method("GET")
     * @Template("MainBundle:Click:index.html.twig")
     * @Secure(roles="ROLE_CLICK_LIST_OWN")
     */
    public function ownAction(Request $request)
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();

        $clicksQuery = $this->em->getRepository('MainBundle:Click')->createNamedQuery('findAllByUser');
        $clicksQuery->setParameter('user', $user);

        return $this->listClicks($clicksQuery, null, null, $request->query->getInt('page', 1));
    }

    /**
     * Lists clicks on owned sites.
     *
     * @Route("/user/{id}", name="click_user")
     * @Method("GET")
     * @Template("MainBundle:Click:index.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @Secure(roles="ROLE_CLICK_LIST_ALL")
     */
    public function userAction(Request $request, User $user)
    {
        $clicksQuery = $this->em->getRepository('MainBundle:Click')->createNamedQuery('findAllByUser');
        $clicksQuery->setParameter('user', $user);

        return $this->listClicks($clicksQuery, $user, null, $request->query->getInt('page', 1));
    }

    /**
     * Lists all Click entities.
     *
     * @Route("/all", name="click_all")
     * @Method("GET")
     * @Template("MainBundle:Click:index.html.twig")
     * @Secure(roles="ROLE_CLICK_LIST_ALL")
     */
    public function allAction(Request $request)
    {
        $clicksQuery = $this->em->getRepository('MainBundle:Click')->createNamedQuery('findAll');

        return $this->listClicks($clicksQuery, null, null, $request->query->getInt('page', 1));
    }

    public function listClicks(Query $clicksQuery, $user = null, $site = null, $page = 1)
    {
        if ($this->securityContext->isGranted('ROLE_CLICK_LIST_ALL')) {
            $clickUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
            $clickUsersQuery->setParameter('user', $this->getUser());
            $clickUsers = $clickUsersQuery->execute();
        } else {
            $clickUsers = array();
        }

        $totalResults = Paginate::count($clicksQuery);
        $paginator = PaginatorHelper::getPaginator($totalResults, $page);
        PaginatorHelper::applyOffsetAndLimit($clicksQuery, $paginator);

        $clicks = $clicksQuery->execute();

        return array(
            'entities' => $clicks,
            'click_users' => $clickUsers,
            'selected_click_site' => $site,
            'selected_click_user' => $user,
            'paginator' => $paginator->getPages(),
        );
    }

    /**
     * Finds and displays a Click entity.
     *
     * @Route("/{id}", name="click_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MainBundle:Click')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Click entity.');
        }

        return array(
            'entity' => $entity,
        );
    }

}
