<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SousCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SousCategorieRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles sub-category data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized sub-category queries
 * Following KISS principle: Simple data access methods
 */
class SousCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SousCategorie::class);
    }

    /**
     * Find all sub-categories ordered by name.
     *
     * @return SousCategorie[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('sc')
            ->orderBy('sc.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sub-categories by category.
     *
     * @param int $categorieId
     * @return SousCategorie[]
     */
    public function findByCategorie(int $categorieId): array
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.categorie = :categorieId')
            ->setParameter('categorieId', $categorieId)
            ->orderBy('sc.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sub-categories by product.
     *
     * @param int $produitId
     * @return SousCategorie[]
     */
    public function findByProduit(int $produitId): array
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('sc.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}