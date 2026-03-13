<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TeinteTablierVolet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeinteTablierVoletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeinteTablierVolet::class);
    }
}
