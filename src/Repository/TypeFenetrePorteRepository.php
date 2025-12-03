<?php

namespace App\Repository;

use App\Entity\TypeFenetrePorte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeFenetrePorte>
 *
 * @method TypeFenetrePorte|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeFenetrePorte|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeFenetrePorte[]    findAll()
 * @method TypeFenetrePorte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeFenetrePorteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeFenetrePorte::class);
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec un système via la table de compatibilité
     *
     * @param int $systemeId
     * @return TypeFenetrePorte[]
     */
    public function findBySysteme(int $systemeId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\TypeFenetrePorteCompatibilite', 'c', 'WITH', 'c.typeFenetrePorte = t')
            ->andWhere('c.systeme = :systemeId')
            ->setParameter('systemeId', $systemeId)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec une ouverture via la table de compatibilité
     *
     * @param int $ouvertureId
     * @return TypeFenetrePorte[]
     */
    public function findByOuverture(int $ouvertureId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\TypeFenetrePorteCompatibilite', 'c', 'WITH', 'c.typeFenetrePorte = t')
            ->andWhere('c.ouverture = :ouvertureId')
            ->setParameter('ouvertureId', $ouvertureId)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les types de fenêtre/porte compatibles avec un système ET une ouverture via la table de compatibilité
     *
     * @param int $systemeId
     * @param int $ouvertureId
     * @return TypeFenetrePorte[]
     */
    public function findBySystemeAndOuverture(int $systemeId, int $ouvertureId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\TypeFenetrePorteCompatibilite', 'c', 'WITH', 'c.typeFenetrePorte = t')
            ->andWhere('c.systeme = :systemeId')
            ->andWhere('c.ouverture = :ouvertureId')
            ->setParameter('systemeId', $systemeId)
            ->setParameter('ouvertureId', $ouvertureId)
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}