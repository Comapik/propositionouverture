<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ouverture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * OuvertureRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles opening types data access
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized opening types queries
 * Following KISS principle: Simple data access methods
 */
class OuvertureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ouverture::class);
    }

    /**
     * Find all opening types ordered by name.
     *
     * @return Ouverture[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find opening types by sub-category.
     *
     * @param int $sousCategorieId
     * @return Ouverture[]
     */
    public function findBySousCategorie(int $sousCategorieId): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.sousCategorie = :sousCategorieId')
            ->setParameter('sousCategorieId', $sousCategorieId)
            ->orderBy('o.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}