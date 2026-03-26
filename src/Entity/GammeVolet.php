<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GammeVolet entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents gamme volet data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized gamme management
 * Following KISS principle: Simple gamme structure
 */
#[ORM\Entity]
#[ORM\Table(name: 'gamme_volet')]
class GammeVolet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la gamme est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
