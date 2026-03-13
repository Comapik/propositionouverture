<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TablierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TablierRepository::class)]
#[ORM\Table(name: 'Tablier')]
class Tablier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'Tablier_type', length: 255, nullable: true)]
    private ?string $tablierType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTablierType(): ?string
    {
        return $this->tablierType;
    }

    public function setTablierType(?string $tablierType): static
    {
        $this->tablierType = $tablierType;

        return $this;
    }
}
