<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Couleur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CouleurRepository following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles Couleur entity database operations
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized color data access
 * Following KISS principle: Simple repository methods
 */
class CouleurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Couleur::class);
    }

    /**
     * Trouve toutes les couleurs
     *
     * @return Couleur[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les couleurs par type (hex ou image)
     *
     * @param bool $isHexColor Si true, retourne les couleurs hex (plaxage_laquage_id = 1), sinon les couleurs avec image
     * @return Couleur[]
     */
    public function findByType(bool $isHexColor): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($isHexColor) {
            $qb->andWhere('c.plaxageLaquageId = 1')
               ->andWhere('c.codeHex IS NOT NULL');
        } else {
            $qb->andWhere('c.plaxageLaquageId = 2')
               ->andWhere('c.urlImage IS NOT NULL');
        }

        return $qb->orderBy('c.nom', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Trouve les couleurs RAL (plaxage_laquage_id = 1)
     *
     * @return Couleur[]
     */
    public function findRALColors(): array
    {
        return $this->findByType(true);
    }

    /**
     * Trouve les couleurs Renolit (plaxage_laquage_id = 2)
     *
     * @return Couleur[]
     */
    public function findRenolitColors(): array
    {
        return $this->findByType(false);
    }

    /**
     * Recherche de couleurs par nom
     *
     * @param string $searchTerm
     * @return Couleur[]
     */
    public function findByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}