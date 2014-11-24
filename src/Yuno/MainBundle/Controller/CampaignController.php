<?php

namespace Yuno\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Yuno\MainBundle\Click\Filter;
use Yuno\MainBundle\Entity\CampaignGroupRepository;
use Yuno\MainBundle\Form\CampaignGroupsType;
use Yuno\MainBundle\Entity\CampaignGroup;
use Yuno\MainBundle\Entity\BannerGroup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Yuno\MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Yuno\MainBundle\Entity\Campaign;
use Yuno\MainBundle\Form\CampaignType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Campaign controller.
 *
 * @Route("/campaign")
 */
class CampaignController extends Controller
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
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface
     *
     * @DI\Inject("form.csrf_provider")
     */
    private $csrfProvider;

    /**
     * @var \Yuno\MainBundle\Util\Encoder
     *
     * @DI\Inject("yuno.encoder")
     */
    private $encoder;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     *
     * @DI\Inject("form.factory")
     */
    private $formFactory;

    /**
     * Lists all owned campaigns.
     *
     * @Route("/", name="campaign")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_CAMPAIGN_LIST_OWN")
     */
    public function indexAction()
    {
        $campaignsQuery = $this->em->getRepository('MainBundle:Campaign')->createNamedQuery('findAllByUser');
        $campaignsQuery->setParameter('user', $this->getUser());

        $campaignUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $campaignUsersQuery->setParameter('user', $this->getUser());
        $campaignUsers = $campaignUsersQuery->execute();

        $campaigns = $campaignsQuery->execute();

        return [
            'campaign_users' => $campaignUsers,
            'entities'       => $campaigns,
        ];
    }

    /**
     * Lists all campaigns by a specific user.
     *
     * @Route("/user/{id}", name="campaign_user")
     * @Method("GET")
     * @Template("MainBundle:Campaign:index.html.twig")
     * @ParamConverter("user", class="MainBundle:User")
     * @Secure(roles="ROLE_CAMPAIGN_LIST_ALL")
     */
    public function userAction(User $user)
    {
        $campaignsQuery = $this->em->getRepository('MainBundle:Campaign')->createNamedQuery('findAllByUser');
        $campaignsQuery->setParameter('user', $user);

        $campaigns = $campaignsQuery->execute();

        $campaignUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $campaignUsersQuery->setParameter('user', $this->getUser());
        $campaignUsers = $campaignUsersQuery->execute();

        return [
            'campaign_users' => $campaignUsers,
            'entities'       => $campaigns,
            'selected_user'  => $user,
        ];
    }

    /**
     * Lists all campaigns.
     *
     * @Route("/all", name="campaign_all")
     * @Method("GET")
     * @Template("MainBundle:Campaign:index.html.twig")
     * @Secure(roles="ROLE_CAMPAIGN_LIST_ALL")
     */
    public function allAction()
    {
        $campaigns = $this->em->getRepository('MainBundle:Campaign')->findAll();

        $campaignUsersQuery = $this->em->getRepository('MainBundle:User')->createNamedQuery('findAllExcept');
        $campaignUsersQuery->setParameter('user', $this->getUser());
        $campaignUsers = $campaignUsersQuery->execute();

        return [
            'campaign_users' => $campaignUsers,
            'entities'       => $campaigns,
        ];
    }

    /**
     * Displays a form to create a new Campaign entity.
     *
     * @Route("/new", name="campaign_new")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_CAMPAIGN_CREATE")
     */
    public function newAction()
    {
        $entity = new Campaign();
        $form   = $this->createForm(new CampaignType($this->securityContext, $this->em), $entity);

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Campaign entity.
     *
     * @Route("/{id}/show", name="campaign_show")
     * @Method("GET")
     * @Template()
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="VIEW")
     */
    public function showAction(Campaign $campaign)
    {
        /** @var $bannerGroupRepository \Yuno\MainBundle\Entity\BannerGroupRepository */
        $bannerGroupRepository = $this->em->getRepository('MainBundle:BannerGroup');

        $bannerGroupsQuery = $bannerGroupRepository->createNamedQuery('findAllByCampaignOrUser');
        $bannerGroupsQuery->setParameter('campaign', $campaign);
        $bannerGroupsQuery->setParameter('user', $campaign->getUser());
        $bannerGroups = $bannerGroupsQuery->execute();

        $bannerCount = $bannerGroupRepository->getBannerCountsForUser($campaign->getUser());

        $campaignGroups = [];
        foreach ($campaign->getCampaignGroups() as $campaignGroup) {
            $campaignGroups[$campaignGroup->getBannerGroup()->getId()] = $campaignGroup;
        }

        return [
            'entity'          => $campaign,
            'banner_groups'   => $bannerGroups,
            'campaign_groups' => $campaignGroups,
            'banner_count'    => $bannerCount,
        ];
    }

    /**
     * Creates a new Campaign entity.
     *
     * @Route("/create", name="campaign_create")
     * @Method("POST")
     * @Template("MainBundle:Campaign:new.html.twig")
     * @Secure(roles="ROLE_CAMPAIGN_CREATE")
     */
    public function createAction(Request $request)
    {
        $entity = new Campaign();
        $form   = $this->createForm(new CampaignType($this->securityContext, $this->em), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('campaign_show', ['id' => $entity->getId()]));
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Campaign entity.
     *
     * @Route("/{id}/edit", name="campaign_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="EDIT")
     *
     */
    public function editAction(Campaign $campaign)
    {
        $editForm = $this->createForm(new CampaignType($this->securityContext, $this->em), $campaign);

        return [
            'entity' => $campaign,
            'form'   => $editForm->createView(),
        ];
    }

    /**
     * Edits an existing Campaign entity.
     *
     * @Route("/{id}/update", name="campaign_update")
     * @Method("PUT")
     * @Template("MainBundle:Campaign:edit.html.twig")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="EDIT")
     */
    public function updateAction(Request $request, Campaign $campaign)
    {
        $editForm = $this->createForm(new CampaignType($this->securityContext, $this->em), $campaign);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $this->em->persist($campaign);
            $this->em->flush();

            return $this->redirect($this->generateUrl('campaign_edit', ['id' => $campaign->getId()]));
        }

        return [
            'entity' => $campaign,
            'form'   => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="campaign_remove")
     * @Method("GET")
     * @Template("MainBundle:Campaign:remove.html.twig")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="DELETE")
     */
    public function removeAction(Campaign $campaign)
    {
        $form = $this->createDeleteForm($campaign);

        return [
            'entity'      => $campaign,
            'delete_form' => $form->createView(),
        ];
    }

    /**
     * Deletes a Campaign entity.
     *
     * @Route("/{id}/delete", name="campaign_delete")
     * @Method("DELETE")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="DELETE")
     */
    public function deleteAction(Request $request, Campaign $campaign)
    {
        $form = $this->createDeleteForm($campaign);
        $form->bind($request);

        if ($form->isValid()) {
            $this->em->remove($campaign);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('campaign'));
    }

    private function createDeleteForm(Campaign $campaign)
    {
        return $this->createFormBuilder(['id' => $campaign->getId()])
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * Sets campaign's active state.
     *
     * @Route("/{id}/set-active/{csrf}", name="campaign_set_active")
     * @Method("GET")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="EDIT")
     */
    public function setActiveAction(Request $request, Campaign $campaign, $csrf)
    {
        if (!$this->csrfProvider->isCsrfTokenValid('campaign-set-active', $csrf)) {
            return new AccessDeniedException('Invalid CSRF token.');
        }

        $campaign->setActive($request->query->getInt('status'));
        $this->em->persist($campaign);
        $this->em->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/{id}/merge-group/{group_id}/{csrf}", name="campaign_merge_group")
     * @Method("GET")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @ParamConverter("bannerGroup", class="MainBundle:BannerGroup", options={"id":"group_id"})
     * @SecureParam(name="campaign", permissions="EDIT")
     */
    public function mergeGroupAction(Request $request, Campaign $campaign, BannerGroup $bannerGroup, $csrf)
    {
        if (!$this->csrfProvider->isCsrfTokenValid('campaign-merge-group', $csrf)) {
            return new AccessDeniedException('Invalid CSRF token.');
        }

        $campaignGroup = $this->em->getRepository('MainBundle:CampaignGroup')->findOneBy(
            [
                'campaign'    => $campaign,
                'bannerGroup' => $bannerGroup,
            ]
        );
        if ($request->query->getInt('status')) {
            if ($campaignGroup === null) {
                $campaignGroup = new CampaignGroup();
                $campaignGroup->setCampaign($campaign);
                $campaignGroup->setBannerGroup($bannerGroup);
                $campaignGroup->setClickLimit(100);

                $this->em->persist($campaignGroup);
                $this->em->flush();
            }
        } else {
            if ($campaignGroup !== null) {
                $this->em->remove($campaignGroup);
                $this->em->flush();
            }
        }

        return new JsonResponse();
    }

    /**
     * @Route("/{id}/overview", name="campaign_overview")
     * @Method({"GET","POST"})
     * @Template()
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="EDIT")
     */
    public function overviewAction(Request $request, Campaign $campaign)
    {
        $form = $this->createForm(new CampaignGroupsType(), $campaign);

        if ($request->isMethod('post')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->em->persist($campaign);
                $this->em->flush();
                $this->session->getFlashBag()->add('success', "Campaign quotas updated.");

                return $this->redirect($this->generateUrl('campaign_overview', ['id' => $campaign->getId()]));
            }
        }

        $filterData = [];
        $filterForm = $this->getCampaignFilterForm($campaign->getTimezone());
        if ($request->query->getInt('filter', 0) === 1) {
            $filterForm->bind($request);
            $filterData = $filterForm->getData();
        }

        /** @var $clickRepository \Yuno\MainBundle\Entity\ClickRepository */
        $clickRepository = $this->em->getRepository('MainBundle:Click');
        if (empty($filterData['date'])) {
            $dateStart = $campaign->getPreviousMidnight();
            $dateEnd   = $campaign->getNextMidnight();
        } else {
            $dateStart = clone $filterData['date'];
            $dateStart->modify('midnight');
            $dateEnd = clone $filterData['date'];
            $dateEnd->modify('midnight +1 day');
        }
        $bannerClickCount = $clickRepository->getCountsForCampaignByBanner($campaign, $dateStart, $dateEnd);
        $groupClickCount  = $clickRepository->getCountsForCampaignByGroup($campaign, $dateStart, $dateEnd);

        $campaignGroupUrls = [];
        foreach ($campaign->getCampaignGroups() as $campaignGroup) {
            $campaignGroupUrls[$campaignGroup->getId()] = rtrim($this->generateUrl('front', [$this->encoder->encrypt($campaignGroup->getId()) => ''], true), '=');
        }

        $campaignGroups = $campaign->getCampaignGroups()->toArray();
        uasort(
            $campaignGroups,
            function (CampaignGroup $a, CampaignGroup $b) {
                return ($a->getBannerGroup()->getName() > $b->getBannerGroup()->getName()) ? 1 : -1;
            }
        );

        return [
            'entity'              => $campaign,
            'form'                => $form->createView(),
            'filter_form'         => $filterForm->createView(),
            'campaign_groups'     => $campaignGroups,
            'campaign_group_urls' => $campaignGroupUrls,
            'banner_click_count'  => $bannerClickCount,
            'group_click_count'   => $groupClickCount,
        ];
    }

    /**
     * @Route("/{id}/click", name="campaign_click")
     * @Method({"GET","POST"})
     * @Template("MainBundle:Campaign:click.html.twig")
     * @ParamConverter("campaign", class="MainBundle:Campaign")
     * @SecureParam(name="campaign", permissions="EDIT")
     */
    public function campaignClickAction(Campaign $campaign, Request $request)
    {
        $form = $this->formFactory->createNamed(
            '',
            'form',
            null,
            [
                'csrf_protection' => false,
            ]
        );
        $form->add(
            $this->formFactory->createNamed(
                'campaignGroup',
                'entity',
                null,
                [
                    'label'         => "Banner group",
                    'constraints'   => [
                        new \Symfony\Component\Validator\Constraints\NotBlank(),
                    ],
                    'class'         => 'MainBundle:CampaignGroup',
                    'query_builder' => function (CampaignGroupRepository $er) use ($campaign) {
                        $qb = $er->createQueryBuilder('cg')
                            ->where('cg.campaign = :campaign')
                            ->setParameter('campaign', $campaign);

                        return $qb;
                    },
                    'property'      => 'bannerGroup.name'
                ]
            )
        );
        $form->add(
            $this->formFactory->createNamed(
                'time',
                'text',
                (new \DateTime('now', new \DateTimeZone($campaign->getTimezone())))->format('H:i:s'),
                [
                    'label'        => "Time",
                    'required'     => false,
                    'constraints'  => [
                        new \Symfony\Component\Validator\Constraints\Time(),
                    ],
                    'widget_addon' => [
                        'type' => 'append',
                        'icon' => 'time'
                    ],
                    'attr'         => [
                        'class' => 'timepicker input-mini',
                    ],
                    'help_block'   => sprintf("Click time in campaign's timezone (%s).", $campaign->getTimezone()),
                ]
            )
        );
        $formServer = $this->formFactory->createNamed(
            'server',
            'form',
            null,
            [
                'show_legend'     => false,
                'label_render'    => false,
                'widget_controls' => false,
            ]
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'REMOTE_ADDR',
                'text',
                $request->server->get('REMOTE_ADDR'),
                [
                    'label'    => 'IP address',
                    'required' => false,
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'HTTP_USER_AGENT',
                'textarea',
                $request->server->get('HTTP_USER_AGENT'),
                [
                    'label'    => 'User agent',
                    'required' => false,
                    'attr'     => [
                        'rows'  => 2,
                        'style' => 'white-space: pre',
                        'class' => 'input-xxlarge',
                    ]
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'HTTP_REFERER',
                'textarea',
                null,
                [
                    'label'    => 'Referrer',
                    'required' => false,
                    'attr'     => [
                        'rows'  => 2,
                        'style' => 'white-space: pre',
                        'class' => 'input-xxlarge',
                    ],
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'GEOIP_CONTINENT_CODE',
                'text',
                null,
                [
                    'label'    => 'Continent code',
                    'required' => false,
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'GEOIP_COUNTRY_CODE',
                'text',
                null,
                [
                    'label'    => 'Country code',
                    'required' => false,
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'GEOIP_REGION',
                'text',
                null,
                [
                    'label'    => 'State/region',
                    'required' => false,
                ]
            )
        );
        $formServer->add(
            $this->formFactory->createNamed(
                'GEOIP_CITY',
                'text',
                null,
                [
                    'label'    => 'City',
                    'required' => false,
                ]
            )
        );
        $form->add($formServer);

        $status = null;
        $log    = null;
        $reason = null;
        if ($request->query->getInt('campaignGroup')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data         = $form->getData();
                $clickRequest = new Request();
                foreach ($data['server'] as $serverKey => $serverData) {
                    $clickRequest->server->set($serverKey, $serverData);
                }
                $filter = new Filter($clickRequest, $data['campaignGroup'], $this->em, $data['time']);
                $status = $filter->getStatus();
                if ($status > 0) {
                    $reason = Filter::getStatuses()[$status];
                } else {
                    $status = 0;
                }
                $log = $filter->getLog();
            }
        }

        return [
            'entity' => $campaign,
            'form'   => $form->createView(),
            'status' => $status,
            'log'    => $log,
            'reason' => $reason,
        ];
    }

    public function getCampaignFilterForm($timezone)
    {
        $form = $this->formFactory->createNamed(
            '',
            'form',
            null,
            []
        );
        $form->add(
            $this->formFactory->createNamed(
                'date',
                'date',
                new \DateTime('now'),
                [
                    'constraints'   => [
                        new \Symfony\Component\Validator\Constraints\Date(),
                    ],
                    'by_reference'  => false,
                    'attr'          => [
                        'class' => 'datepicker',
                    ],
                    'model_timezone' => $timezone,
                ]
            )
        );
        $form->add(
            $this->formFactory->createNamed(
                'filter',
                'hidden',
                1
            )
        );

        return $form;
    }
}
