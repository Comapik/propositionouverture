<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'Caisson_PVC')]
class CaissonPvc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bloc = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBloc(): ?string
    {
        return $this->bloc;
    }

    public function setBloc(?string $bloc): static
    {
        $this->bloc = $bloc;

        return $this;
    }

    public function __toString(): string
    {
        return $this->bloc ?? ('Caisson PVC #' . $this->id);
    }
}
