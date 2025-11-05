<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FournisseurRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles supplier data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized supplier queries
 * Following KISS principle: Simple data access methods
 */
class FournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fournisseur::class);
    }

    /**
     * Find all suppliers ordered by brand name.
     *
     * @return Fournisseur[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find suppliers by product.
     *
     * @param int $produitId
     * @return Fournisseur[]
     */
    public function findByProduit(int $produitId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('f.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
