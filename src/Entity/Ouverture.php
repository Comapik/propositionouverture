<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OuvertureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ouverture entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents opening types data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized opening types management
 * Following KISS principle: Simple opening types structure
 */
#[ORM\Entity(repositoryClass: OuvertureRepository::class)]
#[ORM\Table(name: 'ouverture')]
class Ouverture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'ouverture est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(targetEntity: SousCategorie::class)]
    #[ORM\JoinColumn(name: 'sous_categorie_id')]
    private ?SousCategorie $sousCategorie = null;

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

    public function getSousCategorie(): ?SousCategorie
    {
        return $this->sousCategorie;
    }

    public function setSousCategorie(?SousCategorie $sousCategorie): static
    {
        $this->sousCategorie = $sousCategorie;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Ouverture #' . $this->id;
    }
}