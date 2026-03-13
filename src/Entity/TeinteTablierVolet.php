<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeinteTablierVoletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeinteTablierVoletRepository::class)]
#[ORM\Table(name: 'Teintes_tablier_volet')]
class TeinteTablierVolet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
