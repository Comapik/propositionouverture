<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConfPf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ConfPfRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles door/window configuration data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized configuration queries
 * Following KISS principle: Simple data access methods
 */
class ConfPfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfPf::class);
    }

    /**
     * Find all configurations ordered by creation date.
     *
     * @return ConfPf[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('cp')
            ->orderBy('cp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find configurations by product.
     *
     * @param int $produitId
     * @return ConfPf[]
     */
    public function findByProduit(int $produitId): array
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('cp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}