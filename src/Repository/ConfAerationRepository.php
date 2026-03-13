<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConfAeration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfAeration>
 */
class ConfAerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfAeration::class);
    }

    /**
     * Find a ConfAeration by aeration and position
     */
    public function findByAerationAndPosition($aeration, $position): ?ConfAeration
    {
        return $this->createQueryBuilder('ca')
            ->andWhere('ca.aeration = :aeration')
            ->andWhere('ca.position = :position')
            ->setParameter('aeration', $aeration)
            ->setParameter('position', $position)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
