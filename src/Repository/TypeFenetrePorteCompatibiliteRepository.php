<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypeFenetrePorteCompatibilite;
use App\Entity\Ouverture;
use App\Entity\Systeme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeFenetrePorteCompatibilite>
 */
class TypeFenetrePorteCompatibiliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeFenetrePorteCompatibilite::class);
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec une ouverture ET un système donnés.
     *
     * @param Ouverture $ouverture
     * @param Systeme $systeme
     * @return TypeFenetrePorteCompatibilite[]
     */
    public function findByOuvertureAndSysteme(Ouverture $ouverture, Systeme $systeme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.ouverture = :ouverture')
            ->andWhere('c.systeme = :systeme')
            ->setParameter('ouverture', $ouverture)
            ->setParameter('systeme', $systeme)
            ->orderBy('c.typeFenetrePorte', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec une ouverture donnée (tous systèmes).
     *
     * @param Ouverture $ouverture
     * @return TypeFenetrePorteCompatibilite[]
     */
    public function findByOuverture(Ouverture $ouverture): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.ouverture = :ouverture')
            ->setParameter('ouverture', $ouverture)
            ->orderBy('c.systeme', 'ASC')
            ->addOrderBy('c.typeFenetrePorte', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec un système donné (toutes ouvertures).
     *
     * @param Systeme $systeme
     * @return TypeFenetrePorteCompatibilite[]
     */
    public function findBySysteme(Systeme $systeme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.systeme = :systeme')
            ->setParameter('systeme', $systeme)
            ->orderBy('c.ouverture', 'ASC')
            ->addOrderBy('c.typeFenetrePorte', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les types de fenêtre/porte uniques pour une combinaison ouverture/système.
     *
     * @param Ouverture $ouverture
     * @param Systeme $systeme
     * @return array
     */
    public function findTypesFenetrePorteByOuvertureAndSysteme(Ouverture $ouverture, Systeme $systeme): array
    {
        $compatibilites = $this->createQueryBuilder('c')
            ->join('c.typeFenetrePorte', 'tfp')
            ->andWhere('c.ouverture = :ouverture')
            ->andWhere('c.systeme = :systeme')
            ->setParameter('ouverture', $ouverture)
            ->setParameter('systeme', $systeme)
            ->orderBy('tfp.nom', 'ASC')
            ->getQuery()
            ->getResult();

        // Extraire les types uniques
        $types = [];
        foreach ($compatibilites as $compatibilite) {
            $type = $compatibilite->getTypeFenetrePorte();
            if ($type && !in_array($type, $types, true)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * Vérifie si un type de fenêtre/porte est compatible avec une ouverture et un système.
     *
     * @param int $typeFenetrePorteId
     * @param int $ouvertureId
     * @param int $systemeId
     * @return bool
     */
    public function isCompatible(int $typeFenetrePorteId, int $ouvertureId, int $systemeId): bool
    {
        $result = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.typeFenetrePorte = :typeFenetrePorte')
            ->andWhere('c.ouverture = :ouverture')
            ->andWhere('c.systeme = :systeme')
            ->setParameter('typeFenetrePorte', $typeFenetrePorteId)
            ->setParameter('ouverture', $ouvertureId)
            ->setParameter('systeme', $systemeId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}