<?php

namespace Yuno\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Yuno\MainBundle\Entity\User;
use Yuno\MainBundle\Form\UserType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
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
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_USER_LIST")
     */
    public function indexAction()
    {
        $entities = $this->em->getRepository('MainBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Template("MainBundle:User:new.html.twig")
     * @Secure(roles="ROLE_USER_CREATE")
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createForm(new UserType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_USER_CREATE")
     */
    public function newAction()
    {
        $entity = new User();
        $form = $this->createForm(new UserType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     * @Template()
     * @ParamConverter("user", class="MainBundle:User")
     * @Secure(roles="ROLE_USER_LIST")
     */
    public function showAction(User $user)
    {
        return array(
            'entity' => $user,
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("user", class="MainBundle:User")
     * @SecureParam(name="user", permissions="EDIT")
     */
    public function editAction($user)
    {
        $editForm = $this->createForm(new UserType(), $user);

        return array(
            'entity' => $user,
            'form' => $editForm->createView(),
        );
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("MainBundle:User:edit.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @SecureParam(name="user", permissions="EDIT")
     */
    public function updateAction(Request $request, User $user)
    {
        $editForm = $this->createForm(new UserType(), $user);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->session->getFlashBag()->add('info', "User updated.");

            return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId())));
        }

        return array(
            'entity' => $user,
            'form' => $editForm->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="user_remove")
     * @Method("GET")
     * @Template("MainBundle:User:remove.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @SecureParam(name="user", permissions="DELETE")
     */
    public function removeAction(User $user)
    {
        $form = $this->createDeleteForm($user);

        return array(
            'entity' => $user,
            'delete_form' => $form->createView(),
        );
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     * @ParamConverter("user", class="MainBundle:User")
     * @SecureParam(name="user", permissions="DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->remove($user);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder(array('id' => $user->getId()))
          ->add('id', 'hidden')
          ->getForm();
    }
}
