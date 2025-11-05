<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SousCategorieRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SousCategorie entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents sub-category data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized sub-category management
 * Following KISS principle: Simple sub-category structure
 */
#[ORM\Entity(repositoryClass: SousCategorieRepository::class)]
#[ORM\Table(name: 'sous_categories')]
class SousCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la sous-catégorie est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'sousCategories')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'sousCategories')]
    private ?Categorie $categorie = null;

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

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Sous-catégorie #' . $this->id;
    }
}