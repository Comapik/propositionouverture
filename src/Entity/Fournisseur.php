<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fournisseur entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents supplier/manufacturer data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized supplier management
 * Following KISS principle: Simple supplier structure
 */
#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
#[ORM\Table(name: 'fournisseurs')]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La marque est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $marque = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: 'Le produit est obligatoire')]
    private ?Produit $produit = null;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: Systeme::class)]
    private Collection $systemes;

    public function __construct()
    {
        $this->systemes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;
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

    /**
     * @return Collection<int, Systeme>
     */
    public function getSystemes(): Collection
    {
        return $this->systemes;
    }

    public function addSysteme(Systeme $systeme): static
    {
        if (!$this->systemes->contains($systeme)) {
            $this->systemes->add($systeme);
            $systeme->setFournisseur($this);
        }

        return $this;
    }

    public function removeSysteme(Systeme $systeme): static
    {
        if ($this->systemes->removeElement($systeme)) {
            // set the owning side to null (unless already changed)
            if ($systeme->getFournisseur() === $this) {
                $systeme->setFournisseur(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->marque ?? 'Fournisseur #' . $this->id;
    }
}
