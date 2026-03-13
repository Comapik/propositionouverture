<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExtensionOffre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExtensionOffre>
 */
class ExtensionOffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtensionOffre::class);
    }

    /**
     * Récupère toutes les extensions d'offre
     *
     * @return ExtensionOffre[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
