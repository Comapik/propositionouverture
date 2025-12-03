<?php

namespace App\Entity;

use App\Repository\TypeFenetrePorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeFenetrePorteRepository::class)]
class TypeFenetrePorte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du type de fenêtre/porte est obligatoire')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: Systeme::class, mappedBy: 'typesFenetrePorte')]
    private Collection $systemes;

    #[ORM\ManyToMany(targetEntity: Ouverture::class, inversedBy: 'typesFenetrePorte')]
    #[ORM\JoinTable(name: 'type_fenetre_porte_ouverture')]
    private Collection $ouvertures;

    #[ORM\OneToMany(mappedBy: 'typeFenetrePorte', targetEntity: ConfPf::class)]
    private Collection $confPfs;

    #[ORM\OneToMany(mappedBy: 'typeFenetrePorte', targetEntity: TypeFenetrePorteCompatibilite::class)]
    private Collection $compatibilites;

    public function __construct()
    {
        $this->systemes = new ArrayCollection();
        $this->ouvertures = new ArrayCollection();
        $this->confPfs = new ArrayCollection();
        $this->compatibilites = new ArrayCollection();
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
            $systeme->addTypeFenetrePorte($this);
        }

        return $this;
    }

    public function removeSysteme(Systeme $systeme): static
    {
        if ($this->systemes->removeElement($systeme)) {
            $systeme->removeTypeFenetrePorte($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Ouverture>
     */
    public function getOuvertures(): Collection
    {
        return $this->ouvertures;
    }

    public function addOuverture(Ouverture $ouverture): static
    {
        if (!$this->ouvertures->contains($ouverture)) {
            $this->ouvertures->add($ouverture);
        }

        return $this;
    }

    public function removeOuverture(Ouverture $ouverture): static
    {
        $this->ouvertures->removeElement($ouverture);

        return $this;
    }

    /**
     * @return Collection<int, ConfPf>
     */
    public function getConfPfs(): Collection
    {
        return $this->confPfs;
    }

    public function addConfPf(ConfPf $confPf): static
    {
        if (!$this->confPfs->contains($confPf)) {
            $this->confPfs->add($confPf);
            $confPf->setTypeFenetrePorte($this);
        }

        return $this;
    }

    public function removeConfPf(ConfPf $confPf): static
    {
        if ($this->confPfs->removeElement($confPf)) {
            // set the owning side to null (unless already changed)
            if ($confPf->getTypeFenetrePorte() === $this) {
                $confPf->setTypeFenetrePorte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeFenetrePorteCompatibilite>
     */
    public function getCompatibilites(): Collection
    {
        return $this->compatibilites;
    }

    public function addCompatibilite(TypeFenetrePorteCompatibilite $compatibilite): static
    {
        if (!$this->compatibilites->contains($compatibilite)) {
            $this->compatibilites->add($compatibilite);
            $compatibilite->setTypeFenetrePorte($this);
        }

        return $this;
    }

    public function removeCompatibilite(TypeFenetrePorteCompatibilite $compatibilite): static
    {
        if ($this->compatibilites->removeElement($compatibilite)) {
            // set the owning side to null (unless already changed)
            if ($compatibilite->getTypeFenetrePorte() === $this) {
                $compatibilite->setTypeFenetrePorte(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si ce type est compatible avec une ouverture et un système donnés.
     * Cette méthode sera principalement utilisée par les repositories pour des vérifications rapides.
     */
    public function isCompatibleWith(?Ouverture $ouverture, ?Systeme $systeme): bool
    {
        if (!$ouverture || !$systeme) {
            return false;
        }
        
        // Pour l'instant, on retourne true car la logique de compatibilité
        // est gérée par le repository TypeFenetrePorteCompatibiliteRepository
        return true;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}