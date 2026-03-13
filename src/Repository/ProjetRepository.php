<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Projet repository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Manages Projet entity data access
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on ManagerRegistry abstraction
 * 
 * Following DRY principle: Centralized project data access
 * Following KISS principle: Simple repository methods
 *
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Find all projects with their clients ordered by creation date.
     *
     * @return Projet[]
     */
    public function findAllWithClients(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->addSelect('c')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find projects by client.
     *
     * @return Projet[]
     */
    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent projects (last N days).
     *
     * @return Projet[]
     */
    public function findRecentProjects(int $days = 30): array
    {
        $date = new \DateTimeImmutable();
        $date = $date->modify("-{$days} days");

        return $this->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->addSelect('c')
            ->andWhere('p.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total projects.
     */
    public function countProjects(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Search projects by reference or description.
     *
     * @return Projet[]
     */
    public function searchProjects(string $term): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->addSelect('c')
            ->andWhere('p.refClient LIKE :term OR p.description LIKE :term OR c.nom LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}