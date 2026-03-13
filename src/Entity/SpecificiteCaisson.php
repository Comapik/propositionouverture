<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SpecificiteCaissonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecificiteCaissonRepository::class)]
#[ORM\Table(name: 'Spécificités_caisson')]
class SpecificiteCaisson
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
