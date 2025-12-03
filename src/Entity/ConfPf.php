<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConfPfRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ConfPf entity for door/window configuration following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents door/window configuration data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized configuration management
 * Following KISS principle: Simple configuration structure
 */
#[ORM\Entity(repositoryClass: ConfPfRepository::class)]
#[ORM\Table(name: 'conf_pf')]
class ConfPf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: 'Le produit est obligatoire')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne]
    private ?SousCategorie $sousCategorie = null;

    #[ORM\ManyToOne]
    private ?Ouverture $ouverture = null;

    #[ORM\ManyToOne]
    private ?Fournisseur $fournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'confPfs')]
    private ?Systeme $systeme = null;

    #[ORM\ManyToOne(inversedBy: 'confPfs')]
    private ?TypeFenetrePorte $typeFenetrePorte = null;

    #[ORM\ManyToOne(inversedBy: 'confPfsInterieur')]
    private ?Couleur $couleurInterieur = null;

    #[ORM\ManyToOne(inversedBy: 'confPfsExterieur')]
    private ?Couleur $couleurExterieur = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $largeur = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $hauteur = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La position ne peut pas dépasser {{ limit }} caractères')]
    private ?string $position = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): static
    {
        $this->projet = $projet;
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

    public function getSousCategorie(): ?SousCategorie
    {
        return $this->sousCategorie;
    }

    public function setSousCategorie(?SousCategorie $sousCategorie): static
    {
        $this->sousCategorie = $sousCategorie;
        return $this;
    }

    public function getOuverture(): ?Ouverture
    {
        return $this->ouverture;
    }

    public function setOuverture(?Ouverture $ouverture): static
    {
        $this->ouverture = $ouverture;
        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getLargeur(): ?string
    {
        return $this->largeur;
    }

    public function setLargeur(?string $largeur): static
    {
        $this->largeur = $largeur;
        return $this;
    }

    public function getHauteur(): ?string
    {
        return $this->hauteur;
    }

    public function setHauteur(?string $hauteur): static
    {
        $this->hauteur = $hauteur;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getSysteme(): ?Systeme
    {
        return $this->systeme;
    }

    public function setSysteme(?Systeme $systeme): static
    {
        $this->systeme = $systeme;
        return $this;
    }

    public function getTypeFenetrePorte(): ?TypeFenetrePorte
    {
        return $this->typeFenetrePorte;
    }

    public function setTypeFenetrePorte(?TypeFenetrePorte $typeFenetrePorte): static
    {
        $this->typeFenetrePorte = $typeFenetrePorte;
        return $this;
    }

    public function getCouleurInterieur(): ?Couleur
    {
        return $this->couleurInterieur;
    }

    public function setCouleurInterieur(?Couleur $couleurInterieur): static
    {
        $this->couleurInterieur = $couleurInterieur;
        return $this;
    }

    public function getCouleurExterieur(): ?Couleur
    {
        return $this->couleurExterieur;
    }

    public function setCouleurExterieur(?Couleur $couleurExterieur): static
    {
        $this->couleurExterieur = $couleurExterieur;
        return $this;
    }
}