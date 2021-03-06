<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BannerGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BannerGroupRepository extends EntityRepository
{
    public function getBannerCountsForUser(User $user)
    {
        return $this->getEntityManager()->createQuery('SELECT bg.id, Count(b.id) AS total FROM MainBundle:BannerGroup bg INDEX BY bg.id LEFT JOIN bg.banners b LEFT JOIN b.site s WHERE s.user = :user GROUP BY bg')
            ->setParameter('user', $user)
            ->execute();
    }
}
