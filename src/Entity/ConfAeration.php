<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConfAerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfAeration entity representing the association between aeration and position.
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Represents aeration configuration data
 * - Open/Closed: Can be extended without modification
 */
#[ORM\Entity(repositoryClass: ConfAerationRepository::class)]
#[ORM\Table(name: 'conf_aeration')]
class ConfAeration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'confAerations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Aeration $aeration = null;

    #[ORM\ManyToOne(inversedBy: 'confAerations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Position $position = null;

    #[ORM\OneToMany(targetEntity: ConfPf::class, mappedBy: 'confAeration')]
    private Collection $confPfs;

    public function __construct()
    {
        $this->confPfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAeration(): ?Aeration
    {
        return $this->aeration;
    }

    public function setAeration(?Aeration $aeration): static
    {
        $this->aeration = $aeration;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): static
    {
        $this->position = $position;

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
            $confPf->setConfAeration($this);
        }

        return $this;
    }

    public function removeConfPf(ConfPf $confPf): static
    {
        if ($this->confPfs->removeElement($confPf)) {
            // set the owning side to null (unless already changed)
            if ($confPf->getConfAeration() === $this) {
                $confPf->setConfAeration(null);
            }
        }

        return $this;
    }
}
