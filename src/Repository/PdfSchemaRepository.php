<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PdfSchema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PdfSchema>
 */
class PdfSchemaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfSchema::class);
    }

    /**
     * Récupère tous les schémas PDF actifs triés par ordre
     *
     * @return PdfSchema[]
     */
    public function findActiveOrderedByOrdre(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('p.ordre', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un schéma par son ID s'il est actif
     */
    public function findActiveById(int $id): ?PdfSchema
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.actif = :actif')
            ->setParameter('id', $id)
            ->setParameter('actif', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve le schéma par défaut (premier dans l'ordre)
     */
    public function findDefaultSchema(): ?PdfSchema
    {
        return $this->createQueryBuilder('p')
            ->where('p.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('p.ordre', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}