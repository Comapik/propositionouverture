<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ProduitRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles product data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized product queries
 * Following KISS principle: Simple data access methods
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Find all products ordered by name.
     *
     * @return Produit[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by name (partial match).
     *
     * @param string $nom
     * @return Produit[]
     */
    public function findByNomLike(string $nom): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}