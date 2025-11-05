<?php

namespace App\Entity;

use App\Repository\SystemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SystemeRepository::class)]
class Systeme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du système est obligatoire')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Fournisseur::class, inversedBy: 'systemes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le fournisseur est obligatoire')]
    private ?Fournisseur $fournisseur = null;

    #[ORM\OneToMany(mappedBy: 'systeme', targetEntity: ConfPf::class)]
    private Collection $confPfs;

    public function __construct()
    {
        $this->confPfs = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
            $confPf->setSysteme($this);
        }

        return $this;
    }

    public function removeConfPf(ConfPf $confPf): static
    {
        if ($this->confPfs->removeElement($confPf)) {
            // set the owning side to null (unless already changed)
            if ($confPf->getSysteme() === $this) {
                $confPf->setSysteme(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}