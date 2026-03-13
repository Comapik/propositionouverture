<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Position entity representing aeration positions.
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Represents position data
 * - Open/Closed: Can be extended without modification
 */
#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\Table(name: 'position')]
class Position
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $position = null;

    #[ORM\OneToMany(targetEntity: ConfAeration::class, mappedBy: 'position')]
    private Collection $confAerations;

    public function __construct()
    {
        $this->confAerations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

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
            $confAeration->setPosition($this);
        }

        return $this;
    }

    public function removeConfAeration(ConfAeration $confAeration): static
    {
        if ($this->confAerations->removeElement($confAeration)) {
            // set the owning side to null (unless already changed)
            if ($confAeration->getPosition() === $this) {
                $confAeration->setPosition(null);
            }
        }

        return $this;
    }
}
