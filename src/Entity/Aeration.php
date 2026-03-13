<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Aeration entity representing different aeration options.
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Represents aeration data
 * - Open/Closed: Can be extended without modification
 */
#[ORM\Entity(repositoryClass: AerationRepository::class)]
#[ORM\Table(name: 'aeration')]
class Aeration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $modele = null;

    #[ORM\OneToMany(targetEntity: ConfAeration::class, mappedBy: 'aeration')]
    private Collection $confAerations;

    public function __construct()
    {
        $this->confAerations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    /**
     * @return Collection<int, ConfAeration>
     */
    public function getConfAerations(): Collection
    {
        return $this->confAerations;
    }

    public function addConfAeration(ConfAeration $confAeration): static
    {
        if (!$this->confAerations->contains($confAeration)) {
            $this->confAerations->add($confAeration);
            $confAeration->setAeration($this);
        }

        return $this;
    }

    public function removeConfAeration(ConfAeration $confAeration): static
    {
        if ($this->confAerations->removeElement($confAeration)) {
            // set the owning side to null (unless already changed)
            if ($confAeration->getAeration() === $this) {
                $confAeration->setAeration(null);
            }
        }

        return $this;
    }
}
