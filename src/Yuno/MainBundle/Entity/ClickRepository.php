<?php

namespace Yuno\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ClickRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClickRepository extends EntityRepository
{
    public function getCountsForCampaignByBanner(Campaign $campaign, \DateTime $dateStart, \DateTime $dateEnd)
    {
        $result = $this->getEntityManager()
            ->getConnection()
            ->executeQuery(
                'SELECT b.id, COUNT(c.id) AS total FROM click c INNER JOIN banner b ON c.banner_id = b.id WHERE c.campaign_id = :campaign_id AND c.blocked IS NULL AND c.createdAt BETWEEN :date_start AND :date_end GROUP BY b.id',
                [
                    'campaign_id' => $campaign->getId(),
                    'date_start'  => $dateStart->format('Y-m-d H:i:s'),
                    'date_end'    => $dateEnd->format('Y-m-d H:i:s'),
                ]
            )
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    public function getCountsForCampaignByGroup(Campaign $campaign, \DateTime $dateStart, \DateTime $dateEnd)
    {
        $counts = $this->getEntityManager()
            ->getConnection()
            ->executeQuery(
                'SELECT b.group_id, COUNT(c.id) AS total, SUM(c.blocked IS NULL) AS green, SUM(c.blocked IS NOT NULL) AS red FROM click c INNER JOIN banner b ON c.banner_id = b.id WHERE c.campaign_id = :campaign_id AND c.createdAt BETWEEN :date_start AND :date_end GROUP BY b.group_id',
                [
                    'campaign_id' => $campaign->getId(),
                    'date_start'  => $dateStart->format('Y-m-d H:i:s'),
                    'date_end'    => $dateEnd->format('Y-m-d H:i:s'),
                ]
            )
            ->fetchAll();
        $result = [];
        foreach ($counts as $count) {
            $result[$count['group_id']] = $count;
        }

        return $result;
    }
}
