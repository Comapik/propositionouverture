<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CategorieRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles category data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized category queries
 * Following KISS principle: Simple data access methods
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Find all categories ordered by name.
     *
     * @return Categorie[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find categories by product.
     *
     * @param int $produitId
     * @return Categorie[]
     */
    public function findByProduit(int $produitId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}