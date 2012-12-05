<?php

namespace Yuno\MainBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Yuno\MainBundle\Entity\BannerGroup;
use Yuno\MainBundle\Entity\Banner;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class BannerGroupListener
{
    private $flush = false;

    public function prePersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Banner) {
            return;
        }

        $groupName = $this->getGroupName($entity->getBotUrl());
        $bannerGroup = $this->findOrCreateBannerGroup($args->getEntityManager(), $groupName);

        if (!$bannerGroup->getId()) {
            $args->getEntityManager()->persist($bannerGroup);
            $this->flush = true;
        }

        $entity->setGroup($bannerGroup);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Banner) {
            return;
        }

        if (!$args->hasChangedField('botUrl')) {
            return;
        }

        $oldBannerGroup = $entity->getGroup();
        $groupName = $this->getGroupName($entity->getBotUrl());

        if ($oldBannerGroup->getName() === $groupName) {
            return;
        }

        $bannerGroup = $this->findOrCreateBannerGroup($args->getEntityManager(), $groupName);
        if (!$bannerGroup->getId()) {
            $args->getEntityManager()->persist($bannerGroup);
            $this->flush = true;
        }

        $entity->setGroup($bannerGroup);
        $args->getEntityManager()->getUnitOfWork()->recomputeSingleEntityChangeSet($args->getEntityManager()->getClassMetadata('MainBundle:Banner'), $entity);

        $this->pruneBannerGroup($args->getEntityManager(), $oldBannerGroup, $entity);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if ($this->flush) {
            $args->getEntityManager()->flush();
            $this->flush = false;
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($this->flush) {
            $args->getEntityManager()->flush();
            $this->flush = false;
        }
    }

    private function getGroupName($url)
    {
        preg_match('{^(?:https?://)?(?:www\.)?([a-z0-9-\.]*)}i', $url, $resultUrl);

        return strtolower($resultUrl[1]);
    }

    private function findOrCreateBannerGroup(EntityManager $em, $name)
    {
        $groupQuery = $em->getRepository('MainBundle:BannerGroup')->createNamedQuery('findByName');
        $groupQuery->setParameter('name', $name);
        $group = $groupQuery->getOneOrNullResult();

        if ($group !== null) {
            return $group;
        }

        $newGroup = new BannerGroup();
        $newGroup->setName($name);

        return $newGroup;
    }

    private function pruneBannerGroup(EntityManager $em, BannerGroup $group, Banner $banner)
    {
        $oldBannerGroupCountQuery = $em->getRepository('MainBundle:BannerGroup')->createNamedQuery('countBannersExcept');
        $oldBannerGroupCountQuery->setParameter('group', $group);
        $oldBannerGroupCountQuery->setParameter('banner', $banner);
        $oldBannerGroupCount = $oldBannerGroupCountQuery->getSingleScalarResult();
        if ((int) $oldBannerGroupCount === 0) {
            $em->remove($group);
            $this->flush = true;
        }
    }
}