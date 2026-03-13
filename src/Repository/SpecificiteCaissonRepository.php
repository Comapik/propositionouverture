<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SpecificiteCaisson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SpecificiteCaissonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecificiteCaisson::class);
    }
}
