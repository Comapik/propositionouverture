<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LigneDeCommandeBlocNRID4;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LigneDeCommandeBlocNRID4Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneDeCommandeBlocNRID4::class);
    }
}
