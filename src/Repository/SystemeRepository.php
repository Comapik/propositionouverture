<?php

namespace App\Repository;

use App\Entity\Systeme;
use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Systeme>
 */
class SystemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Systeme::class);
    }

    /**
     * Trouve tous les systèmes d'un fournisseur donné
     */
    public function findByFournisseur(Fournisseur $fournisseur): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.fournisseur = :fournisseur')
            ->setParameter('fournisseur', $fournisseur)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les systèmes d'un fournisseur par son ID
     */
    public function findByFournisseurId(int $fournisseurId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.fournisseur = :fournisseurId')
            ->setParameter('fournisseurId', $fournisseurId)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les systèmes d'un fournisseur et d'une ouverture donnés
     */
    public function findByFournisseurAndOuverture(int $fournisseurId, int $ouvertureId): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.ouvertures', 'o')
            ->andWhere('s.fournisseur = :fournisseurId')
            ->andWhere('o.id = :ouvertureId')
            ->setParameter('fournisseurId', $fournisseurId)
            ->setParameter('ouvertureId', $ouvertureId)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les systèmes d'une ouverture donnée
     */
    public function findByOuvertureId(int $ouvertureId): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.ouvertures', 'o')
            ->andWhere('o.id = :ouvertureId')
            ->setParameter('ouvertureId', $ouvertureId)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Systeme[] Returns an array of Systeme objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Systeme
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}