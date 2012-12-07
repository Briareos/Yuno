<?php

namespace Yuno\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Yuno\MainBundle\Site\Communicator;
use Yuno\MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Yuno\MainBundle\Entity\Site;
use Yuno\MainBundle\Form\SiteType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * Site controller.
 *
 * @Route("/site")
 */
class SiteController extends Controller
{
    /**
     * @var \Doctrine\ORM\EntityManager
     *
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     *
     * @DI\Inject("session")
     */
    private $session;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     *
     * @DI\Inject("security.context")
     */
    private $securityContext;

    /**
     * @var \Yuno\MainBundle\Util\Encoder
     *
     * @DI\Inject("yuno.encoder")
     */
    private $encoder;

    /**
     * Lists all owned sites.
     *
     * @Route("/", name="site")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_SITE_LIST_OWN")
     */
    public function indexAction()
    {
        $sitesQuery = $this->em->getRepository('MainBundle:Site')->createNamedQuery('findAllByUser');
        $sitesQuery->setParameter('user', $this->getUser());

        $siteUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $siteUsersQuery->setParameter('user', $this->getUser());
        $siteUsers = $siteUsersQuery->execute();

        $sites = $sitesQuery->execute();

        return array(
            'site_users' => $siteUsers,
            'entities' => $sites,
        );
    }

    /**
     * Lists all sites by a specific user.
     *
     * @Route("/user/{id}", name="site_user")
     * @Method("GET")
     * @Template("MainBundle:Site:index.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @Secure(roles="ROLE_SITE_LIST_ALL")
     */
    public function userAction(User $user)
    {
        $sitesQuery = $this->em->getRepository('MainBundle:Site')->createNamedQuery('findAllByUser');
        $sitesQuery->setParameter('user', $user);

        $sites = $sitesQuery->execute();

        $siteUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $siteUsersQuery->setParameter('user', $this->getUser());
        $siteUsers = $siteUsersQuery->execute();

        return array(
            'site_users' => $siteUsers,
            'entities' => $sites,
            'selected_user' => $user,
        );
    }

    /**
     * Lists all sites.
     *
     * @Route("/all", name="site_all")
     * @Method("GET")
     * @Template("MainBundle:Site:index.html.twig")
     * @Secure(roles="ROLE_SITE_LIST_ALL")
     */
    public function allAction()
    {
        $sites = $this->em->getRepository('MainBundle:Site')->findAll();

        $siteUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $siteUsersQuery->setParameter('user', $this->getUser());
        $siteUsers = $siteUsersQuery->execute();

        return array(
            'site_users' => $siteUsers,
            'entities' => $sites,
        );
    }

    /**
     * Creates a new Site entity.
     *
     * @Route("/", name="site_create")
     * @Method("POST")
     * @Template("MainBundle:Site:new.html.twig")
     * @Secure(roles="ROLE_SITE_CREATE")
     */
    public function createAction(Request $request)
    {
        $entity = new Site();
        $form = $this->createForm(new SiteType($this->securityContext), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('site_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Site entity.
     *
     * @Route("/new", name="site_new")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_SITE_CREATE")
     */
    public function newAction()
    {
        $entity = new Site();
        $form = $this->createForm(new SiteType($this->securityContext), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Site entity.
     *
     * @Route("/{id}", name="site_show")
     * @Method("GET")
     * @Template()
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="VIEW")
     */
    public function showAction(Site $site)
    {
        return array(
            'entity' => $site,
        );
    }

    /**
     * Displays a form to edit an existing Site entity.
     *
     * @Route("/{id}/edit", name="site_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function editAction(Site $site)
    {
        $editForm = $this->createForm(new SiteType($this->securityContext), $site);

        return array(
            'entity' => $site,
            'form' => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Site entity.
     *
     * @Route("/{id}", name="site_update")
     * @Method("PUT")
     * @Template("MainBundle:Site:edit.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function updateAction(Request $request, Site $site)
    {
        $editForm = $this->createForm(new SiteType($this->securityContext), $site);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $this->em->persist($site);
            $this->em->flush();

            $this->session->getFlashBag()->add('success', "Site updated.");

            return $this->redirect($this->generateUrl('site_edit', array('id' => $site->getId())));
        }

        return array(
            'entity' => $site,
            'form' => $editForm->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="site_remove")
     * @Method("GET")
     * @Template("MainBundle:Site:remove.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="DELETE")
     */
    public function removeAction(Site $site)
    {
        $form = $this->createDeleteForm($site);

        return array(
            'entity' => $site,
            'delete_form' => $form->createView(),
        );
    }

    /**
     * Deletes a Site entity.
     *
     * @Route("/{id}", name="site_delete")
     * @Method("DELETE")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="DELETE")
     */
    public function deleteAction(Request $request, Site $site)
    {
        $form = $this->createDeleteForm($site);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->remove($site);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('site'));
    }

    /**
     * Creates a form to delete a Site entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Site $site)
    {
        return $this->createFormBuilder(array('id' => $site->getId()))
          ->add('id', 'hidden')
          ->getForm();
    }

    /**
     * Internal action, not accessible via regular requests.
     */
    public function selectWarningAction(Request $request)
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();
        if ($user->getSelectedSite() === null) {
            return $this->render('MainBundle:Site:select_warning.html.twig');
        }

        return $this->redirect($request->server->get('HTTP_REFERER', $this->generateUrl('site')));
    }

    /**
     * @Route("/{id}/select", name="site_select")
     * @Method("GET")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function selectAction(Request $request, Site $site)
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();
        $user->setSelectedSite($site);
        $this->em->persist($user);
        $this->em->flush();

        return $this->redirect($request->server->get('HTTP_REFERER', $this->generateUrl('site')));
    }

    /**
     * @Route("/{id}/update-categories", name="site_update_categories")
     * @Method("GET")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function updateCategoriesAction(Request $request, Site $site)
    {
        $communicator = new Communicator($site);
        $categories = $communicator->getCategories();
        if (is_array($categories) && count($categories)) {
            $site->setCategories($categories);
            $this->em->persist($site);
            $this->em->flush();
            $this->session->getFlashBag()->add('success', sprintf("%s categories successfully fetched.", count($categories)));
        } else {
            $this->session->getFlashBag()->add('error', sprintf("There was an error while fetching categories. Does the secret key match? Is the site URL correct?."));
        }

        return $this->redirect($request->server->get('HTTP_REFERER', $this->generateUrl('site')));
    }

    /**
     * @Route("/{id}/update-banners", name="site_update_banners")
     * @Method("GET")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function updateBannersAction(Request $request, Site $site)
    {
        $communicator = new Communicator($site);
        /** @var $banners \Yuno\MainBundle\Entity\Banner[] */
        $banners = $this->em->getRepository('MainBundle:Banner')->findBy(array('site' => $site));
        $bannerData = array();
        foreach ($banners as $banner) {
            $bannerData[$banner->getId()] = array(
                'id' => $banner->getId(),
                'code' => $banner->getCode(),
                'category' => $banner->getCategory(),
                'group' => $banner->getGroup()->getName(),
                'size' => $banner->getSize(),
            );
        }
        $status = $communicator->setBanners($bannerData);
        if ($status) {
            $this->session->getFlashBag()->add('success', sprintf("%s banners synchronized.", count($banners)));
        } else {
            $this->session->getFlashBag()->add('error', sprintf("There was an error while trying to synchronize banners. Does the secret key match? Is the site URL correct?."));
        }

        return $this->redirect($request->server->get('HTTP_REFERER', $this->generateUrl('site')));
    }

    /**
     * @Route("/{id}/loopback/search-engine", name="site_loopback_search")
     * @Method("GET")
     * @Template("MainBundle:Site:loopback.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function loopbackSearchEngineAction(Site $site)
    {
        return array(
            'entity' => $site,
        );
    }

    /**
     * @Route("/{id}/loopback/yuno", name="site_loopback_yuno")
     * @Method("GET")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function loopbackYunoAction(Site $site, Request $request)
    {
        $parameters = array(
            'yuno',
            time(),
            $request->server->get('REMOTE_ADDR'),
            $this->generateUrl('site_loopback_return', array('id' => $site->getId(), 'time' => microtime(true)), true),
            0,
            1,
        );

        $url = rtrim($site->getUrl(), '/') . '/?' . $this->encoder->encrypt(implode('#', $parameters));

        return $this->redirect($url);
    }

    /**
     * @Route("/{id}/loopback/return/{time}", name="site_loopback_return")
     * @Method("GET")
     * @Template("MainBundle:Site:loopback.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @SecureParam(name="site", permissions="EDIT")
     */
    public function loopbackReturnAction(Site $site, $time, Request $request)
    {
        $result = microtime(true) - (float) $time;

        return array(
            'entity' => $site,
            'time' => $result,
            'referrer' => $request->server->get('HTTP_REFERER'),
            'origin' => $request->server->get('HTTP_ORIGIN'),
        );
    }
}
