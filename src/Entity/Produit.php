<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Produit entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents product data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized product management
 * Following KISS principle: Simple product structure
 */
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produits')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Categorie::class)]
    private Collection $categories;

    /**
     * @var Collection<int, SousCategorie>
     */
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: SousCategorie::class)]
    private Collection $sousCategories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->sousCategories = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
            $categorie->setProduit($this);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        if ($this->categories->removeElement($categorie)) {
            if ($categorie->getProduit() === $this) {
                $categorie->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SousCategorie>
     */
    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategorie(SousCategorie $sousCategorie): static
    {
        if (!$this->sousCategories->contains($sousCategorie)) {
            $this->sousCategories->add($sousCategorie);
            $sousCategorie->setProduit($this);
        }

        return $this;
    }

    public function removeSousCategorie(SousCategorie $sousCategorie): static
    {
        if ($this->sousCategories->removeElement($sousCategorie)) {
            if ($sousCategorie->getProduit() === $this) {
                $sousCategorie->setProduit(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Produit #' . $this->id;
    }
}