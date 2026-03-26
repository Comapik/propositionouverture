<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConfVolet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ConfVoletRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Data access for ConfVolet
 * - Dependency Inversion: Depends on ManagerRegistry abstraction
 * 
 * Following DRY principle: Centralized data access logic
 * Following KISS principle: Simple query methods
 */
class ConfVoletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfVolet::class);
    }

    /**
     * Find all configurations for a specific project
     * 
     * @param int $projetId
     * @return ConfVolet[]
     */
    public function findByProjet(int $projetId): array
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.projet = :projetId')
            ->setParameter('projetId', $projetId)
            ->orderBy('cv.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find configurations by gamme
     * 
     * @param int $gammeVoletId
     * @return ConfVolet[]
     */
    public function findByGamme(int $gammeVoletId): array
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.gammeVolet = :gammeVoletId')
            ->setParameter('gammeVoletId', $gammeVoletId)
            ->orderBy('cv.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
