<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Client repository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Manages Client entity data access
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on ManagerRegistry abstraction
 * 
 * Following DRY principle: Centralized client data access
 * Following KISS principle: Simple repository methods
 *
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Find clients by name (partial match).
     *
     * @return Client[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nom LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find clients with projects.
     *
     * @return Client[]
     */
    public function findClientsWithProjects(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.projets', 'p')
            ->andWhere('p.id IS NOT NULL')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total clients.
     */
    public function countClients(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find client by email.
     */
    public function findByEmail(string $email): ?Client
    {
        return $this->findOneBy(['email' => $email]);
    }
}