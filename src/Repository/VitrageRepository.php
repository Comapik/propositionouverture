<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vitrage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vitrage>
 */
class VitrageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vitrage::class);
    }

    /**
     * @return Vitrage[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.type', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
