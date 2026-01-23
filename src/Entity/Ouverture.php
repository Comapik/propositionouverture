<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OuvertureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL de l\'image doit être valide')]
    private ?string $urlImage = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(choices: ['int', 'ext'], message: 'Le sens d\'ouverture doit être "int" ou "ext".')]
    private ?string $sensOuverture = null;

    #[ORM\ManyToOne(targetEntity: SousCategorie::class)]
    #[ORM\JoinColumn(name: 'sous_categorie_id')]
    private ?SousCategorie $sousCategorie = null;

    #[ORM\ManyToMany(targetEntity: Systeme::class, mappedBy: 'ouvertures')]
    private Collection $systemes;

    #[ORM\ManyToMany(targetEntity: TypeFenetrePorte::class, mappedBy: 'ouvertures')]
    private Collection $typesFenetrePorte;

    public function __construct()
    {
        $this->systemes = new ArrayCollection();
        $this->typesFenetrePorte = new ArrayCollection();
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

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(?string $urlImage): static
    {
        $this->urlImage = $urlImage;
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
            $systeme->addOuverture($this);
        }

        return $this;
    }

    public function removeSysteme(Systeme $systeme): static
    {
        if ($this->systemes->removeElement($systeme)) {
            $systeme->removeOuverture($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeFenetrePorte>
     */
    public function getTypesFenetrePorte(): Collection
    {
        return $this->typesFenetrePorte;
    }

    public function addTypeFenetrePorte(TypeFenetrePorte $typeFenetrePorte): static
    {
        if (!$this->typesFenetrePorte->contains($typeFenetrePorte)) {
            $this->typesFenetrePorte->add($typeFenetrePorte);
            $typeFenetrePorte->addOuverture($this);
        }

        return $this;
    }

    public function removeTypeFenetrePorte(TypeFenetrePorte $typeFenetrePorte): static
    {
        if ($this->typesFenetrePorte->removeElement($typeFenetrePorte)) {
            $typeFenetrePorte->removeOuverture($this);
        }

        return $this;
    }

    public function getSensOuverture(): ?string
    {
        return $this->sensOuverture;
    }

    public function setSensOuverture(?string $sensOuverture): static
    {
        $this->sensOuverture = $sensOuverture;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Ouverture #' . $this->id;
    }
}