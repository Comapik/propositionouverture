<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VitrageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VitrageRepository::class)]
#[ORM\Table(name: 'vitrage')]
class Vitrage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rw = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $epaisseur = null;

    #[ORM\OneToMany(targetEntity: ConfPf::class, mappedBy: 'vitrage')]
    private Collection $confPfs;

    public function __construct()
    {
        $this->confPfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRw(): ?string
    {
        return $this->rw;
    }

    public function setRw(?string $rw): static
    {
        $this->rw = $rw;

        return $this;
    }

    public function getEpaisseur(): ?string
    {
        return $this->epaisseur;
    }

    public function setEpaisseur(?string $epaisseur): static
    {
        $this->epaisseur = $epaisseur;

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
            $confPf->setVitrage($this);
        }

        return $this;
    }

    public function removeConfPf(ConfPf $confPf): static
    {
        if ($this->confPfs->removeElement($confPf)) {
            if ($confPf->getVitrage() === $this) {
                $confPf->setVitrage(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->type ?? '';
    }
}
