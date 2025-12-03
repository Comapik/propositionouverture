<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjetPdf;
use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjetPdf>
 */
class ProjetPdfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetPdf::class);
    }

    /**
     * Trouve tous les PDFs d'un projet triés par date de création décroissante
     */
    public function findByProjetOrderedByDate(Projet $projet): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.projet = :projet')
            ->setParameter('projet', $projet)
            ->orderBy('pp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le PDF le plus récent d'un projet
     */
    public function findLatestByProjet(Projet $projet): ?ProjetPdf
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.projet = :projet')
            ->setParameter('projet', $projet)
            ->orderBy('pp.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les PDFs d'une configuration spécifique
     */
    public function findByConfPf($confPf): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.confPf = :confPf')
            ->setParameter('confPf', $confPf)
            ->orderBy('pp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de PDFs générés pour un projet
     */
    public function countByProjet(Projet $projet): int
    {
        return $this->createQueryBuilder('pp')
            ->select('COUNT(pp.id)')
            ->where('pp.projet = :projet')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getSingleScalarResult();
    }
}