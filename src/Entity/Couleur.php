<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CouleurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Couleur entity for color management following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents color data and configuration
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized color management
 * Following KISS principle: Simple color structure
 */
#[ORM\Entity(repositoryClass: CouleurRepository::class)]
#[ORM\Table(name: 'couleur')]
class Couleur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la couleur est obligatoire')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'Le code couleur doit être au format hexadécimal (#RRGGBB)'
    )]
    private ?string $codeHex = null;

    #[ORM\Column(nullable: true)]
    private ?int $plaxageLaquageId = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'URL de l\'image doit être valide')]
    #[Assert\Length(max: 500, maxMessage: 'L\'URL ne peut pas dépasser {{ limit }} caractères')]
    private ?string $urlImage = null;

    #[ORM\OneToMany(mappedBy: 'couleurInterieur', targetEntity: ConfPf::class)]
    private Collection $confPfsInterieur;

    #[ORM\OneToMany(mappedBy: 'couleurExterieur', targetEntity: ConfPf::class)]
    private Collection $confPfsExterieur;

    public function __construct()
    {
        $this->confPfsInterieur = new ArrayCollection();
        $this->confPfsExterieur = new ArrayCollection();
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

    public function getCodeHex(): ?string
    {
        return $this->codeHex;
    }

    public function setCodeHex(?string $codeHex): static
    {
        $this->codeHex = $codeHex;
        return $this;
    }

    public function getPlaxageLaquageId(): ?int
    {
        return $this->plaxageLaquageId;
    }

    public function setPlaxageLaquageId(?int $plaxageLaquageId): static
    {
        $this->plaxageLaquageId = $plaxageLaquageId;
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

    /**
     * @return Collection<int, ConfPf>
     */
    public function getConfPfsInterieur(): Collection
    {
        return $this->confPfsInterieur;
    }

    public function addConfPfInterieur(ConfPf $confPf): static
    {
        if (!$this->confPfsInterieur->contains($confPf)) {
            $this->confPfsInterieur->add($confPf);
            $confPf->setCouleurInterieur($this);
        }

        return $this;
    }

    public function removeConfPfInterieur(ConfPf $confPf): static
    {
        if ($this->confPfsInterieur->removeElement($confPf)) {
            // set the owning side to null (unless already changed)
            if ($confPf->getCouleurInterieur() === $this) {
                $confPf->setCouleurInterieur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfPf>
     */
    public function getConfPfsExterieur(): Collection
    {
        return $this->confPfsExterieur;
    }

    public function addConfPfExterieur(ConfPf $confPf): static
    {
        if (!$this->confPfsExterieur->contains($confPf)) {
            $this->confPfsExterieur->add($confPf);
            $confPf->setCouleurExterieur($this);
        }

        return $this;
    }

    public function removeConfPfExterieur(ConfPf $confPf): static
    {
        if ($this->confPfsExterieur->removeElement($confPf)) {
            // set the owning side to null (unless already changed)
            if ($confPf->getCouleurExterieur() === $this) {
                $confPf->setCouleurExterieur(null);
            }
        }

        return $this;
    }

    /**
     * Détermine si la couleur utilise un code hexadécimal ou une image
     * Selon les instructions: si plaxage_laquage_id = 1 alors code hex couleur, sinon lien url vers image
     */
    public function isHexColor(): bool
    {
        return $this->plaxageLaquageId === 1;
    }

    /**
     * Retourne la représentation visuelle de la couleur (hex ou image)
     */
    public function getColorRepresentation(): ?string
    {
        return $this->isHexColor() ? $this->codeHex : $this->urlImage;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}