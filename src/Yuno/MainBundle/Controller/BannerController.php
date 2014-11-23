<?php

namespace Yuno\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Yuno\MainBundle\Banner\InfoGuesser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Yuno\MainBundle\Entity\User;
use Doctrine\ORM\Query;
use DoctrineExtensions\Paginate\Paginate;
use Yuno\MainBundle\Entity\Site;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Yuno\MainBundle\Entity\Banner;
use Yuno\MainBundle\Form\BannerType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Yuno\MainBundle\Paginator\PaginatorHelper;

/**
 * Banner controller.
 *
 * @Route("/banner")
 */
class BannerController extends Controller
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
     * Lists banners for user's currently selected site.
     *
     * @Route("/", name="banner")
     * @Method("GET")
     * @Template("MainBundle:Banner:index.html.twig")
     * @Secure(roles="ROLE_BANNER_LIST_OWN")
     */
    public function indexAction()
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();
        $site = $user->getSelectedSite();

        $bannersQuery = $this->em->getRepository('MainBundle:Banner')->createNamedQuery('findAllBySite');
        $bannersQuery->setParameter('site', $site);

        return $this->listBanners($bannersQuery, null, null, $this->getRequest()->query->getInt('page', 1));
    }

    /**
     * List banners for the site.
     *
     * @Route("/site/{id}", name="banner_site")
     * @Method("GET")
     * @Template("MainBundle:Banner:index.html.twig")
     * @ParamConverter("site", class="MainBundle:Site")
     * @Secure(roles="ROLE_BANNER_LIST_ALL")
     */
    public function siteAction(Site $site, Request $request)
    {
        $bannersQuery = $this->em->getRepository('MainBundle:Banner')->createNamedQuery('findAllBySite');
        $bannersQuery->setParameter('site', $site);

        return $this->listBanners($bannersQuery, null, $site, $request->query->getInt('page', 1));
    }

    /**
     * Lists all owned banners.
     *
     * @Route("/own", name="banner_own")
     * @Method("GET")
     * @Template("MainBundle:Banner:index.html.twig")
     * @Secure(roles="ROLE_BANNER_LIST_OWN")
     */
    public function ownAction(Request $request)
    {
        /** @var $user \Yuno\MainBundle\Entity\User */
        $user = $this->getUser();

        $bannersQuery = $this->em->getRepository('MainBundle:Banner')->createNamedQuery('findAllByUser');
        $bannersQuery->setParameter('user', $user);

        return $this->listBanners($bannersQuery, null, null, $request->query->getInt('page', 1));
    }

    /**
     * Lists all banners owned by a user.
     *
     * @Route("/user/{id}", name="banner_user")
     * @Method("GET")
     * @Template("MainBundle:Banner:index.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @Secure(roles="ROLE_BANNER_LIST_ALL")
     */
    public function userAction(Request $request, User $user)
    {
        $bannersQuery = $this->em->getRepository('MainBundle:Banner')->createNamedQuery('findAllByUser');
        $bannersQuery->setParameter('user', $user);

        return $this->listBanners($bannersQuery, $user, null, $request->query->getInt('page', 1));
    }

    /**
     * List all banners.
     *
     * @Route("/all", name="banner_all")
     * @Method("GET")
     * @Template("MainBundle:Banner:index.html.twig")
     * @Secure(roles="ROLE_BANNER_LIST_ALL")
     */
    public function allAction(Request $request)
    {
        $bannersQuery = $this->em->getRepository('MainBundle:Banner')->createNamedQuery('findAll');

        return $this->listBanners($bannersQuery, null, null, $request->query->getInt('page', 1));
    }

    public function listBanners(Query $bannersQuery, $user = null, $site = null, $page = 1)
    {
        if ($this->securityContext->isGranted('ROLE_BANNER_LIST_ALL')) {
            $bannerUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
            $bannerUsersQuery->setParameter('user', $this->getUser());
            $bannerUsers = $bannerUsersQuery->execute();
        } else {
            $bannerUsers = [];
        }

        $totalResults = Paginate::count($bannersQuery);
        $paginator    = PaginatorHelper::getPaginator($totalResults, $page);
        PaginatorHelper::applyOffsetAndLimit($bannersQuery, $paginator);

        $banners = $bannersQuery->execute();

        return [
            'entities'             => $banners,
            'banner_users'         => $bannerUsers,
            'selected_banner_site' => $site,
            'selected_banner_user' => $user,
            'paginator'            => $paginator->getPages(),
        ];
    }

    /**
     * Creates a new Banner entity.
     *
     * @Route("/", name="banner_create")
     * @Method("POST")
     * @Template("MainBundle:Banner:new.html.twig")
     * @Secure(roles="ROLE_BANNER_CREATE")
     */
    public function createAction(Request $request)
    {
        $entity = new Banner();
        $form   = $this->createForm(new BannerType($this->securityContext), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //return $this->redirect($this->generateUrl('banner_show', array('id' => $entity->getId())));
            $this->session->getFlashBag()->add('success', 'Banner successfully added.');

            return $this->redirect($this->generateUrl('banner_new'));
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new Banner entity.
     *
     * @Route("/new", name="banner_new")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_BANNER_CREATE")
     */
    public function newAction()
    {
        $banner = new Banner();
        $form   = $this->createForm(new BannerType($this->securityContext), $banner);

        return [
            'entity' => $banner,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Banner entity.
     *
     * @Route("/{id}", name="banner_show")
     * @Method("GET")
     * @Template()
     * @ParamConverter("banner", class="MainBundle:Banner")
     * @SecureParam(name="banner", permissions="VIEW")
     */
    public function showAction(Banner $banner)
    {
        return [
            'entity' => $banner,
        ];
    }

    /**
     * Displays a form to edit an existing Banner entity.
     *
     * @Route("/{id}/edit", name="banner_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("banner", class="MainBundle:Banner")
     * @SecureParam(name="banner", permissions="EDIT")
     */
    public function editAction(Banner $banner)
    {
        $editForm = $this->createForm(new BannerType($this->securityContext), $banner);

        return [
            'entity' => $banner,
            'form'   => $editForm->createView(),
        ];
    }

    /**
     * Edits an existing Banner entity.
     *
     * @Route("/{id}", name="banner_update")
     * @Method("PUT")
     * @Template("MainBundle:Banner:edit.html.twig")
     * @ParamConverter("banner", class="MainBundle:Banner")
     * @SecureParam(name="banner", permissions="EDIT")
     */
    public function updateAction(Request $request, Banner $banner)
    {
        $editForm = $this->createForm(new BannerType($this->securityContext), $banner);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $this->em->persist($banner);
            $this->em->flush();

            return $this->redirect($this->generateUrl('banner_edit', ['id' => $banner->getId()]));
        }

        return [
            'entity' => $banner,
            'form'   => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="banner_remove")
     * @Method("GET")
     * @Template("MainBundle:Banner:remove.html.twig")
     * @ParamConverter("banner", class="MainBundle:Banner")
     * @SecureParam(name="banner", permissions="DELETE")
     */
    public function removeAction(Banner $banner)
    {
        $form = $this->createDeleteForm($banner);

        return [
            'entity'      => $banner,
            'delete_form' => $form->createView(),
        ];
    }

    /**
     * Deletes a Banner entity.
     *
     * @Route("/{id}", name="banner_delete")
     * @Method("DELETE")
     * @ParamConverter("banner", class="MainBundle:Banner")
     * @SecureParam(name="banner", permissions="DELETE")
     */
    public function deleteAction(Request $request, Banner $banner)
    {
        $form = $this->createDeleteForm($banner);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->remove($banner);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('banner'));
    }

    /**
     * Creates a form to delete a Banner entity by id.
     *
     * @param Banner $banner The banner
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Banner $banner)
    {
        return $this->createFormBuilder(['id' => $banner->getId()])
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * @Route("/guess-info", name="banner_guess_info")
     * @Method("POST")
     * @Secure(roles="ROLE_BANNER_CREATE")
     */
    public function guessInfoAction(Request $request)
    {
        $code = $request->request->get('code');

        $infoGuesser = new InfoGuesser($code);

        $data = [
            'humanUrl' => $infoGuesser->guessHumanUrl(),
            'botUrl'   => $infoGuesser->guessBotUrl(),
            'size'     => implode('x', $infoGuesser->guessSize()),
        ];

        return new JsonResponse($data);
    }
}
