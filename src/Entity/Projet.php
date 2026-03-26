<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Projet entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents project data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized project management
 * Following KISS principle: Simple project structure
 */
#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projets')]
#[ORM\HasLifecycleCallbacks]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La référence client est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $refClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $confPfId = null;

    /**
     * @var Collection<int, ConfVolet>
     */
    #[ORM\OneToMany(targetEntity: ConfVolet::class, mappedBy: 'projet', cascade: ['persist', 'remove'])]
    private Collection $confVolets;

    #[ORM\OneToOne(targetEntity: ConfVolet::class)]
    #[ORM\JoinColumn(name: 'conf_volet_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ConfVolet $confVolet = null;

    public function __construct()
    {
        $this->confVolets = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getRefClient(): ?string
    {
        return $this->refClient;
    }

    public function setRefClient(string $refClient): static
    {
        $this->refClient = $refClient;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getConfPfId(): ?int
    {
        return $this->confPfId;
    }

    public function setConfPfId(?int $confPfId): static
    {
        $this->confPfId = $confPfId;
        return $this;
    }

    /**
     * @return Collection<int, ConfVolet>
     */
    public function getConfVolets(): Collection
    {
        return $this->confVolets;
    }

    public function addConfVolet(ConfVolet $confVolet): static
    {
        if (!$this->confVolets->contains($confVolet)) {
            $this->confVolets->add($confVolet);
            $confVolet->setProjet($this);
        }

        return $this;
    }

    public function removeConfVolet(ConfVolet $confVolet): static
    {
        if ($this->confVolets->removeElement($confVolet)) {
            // set the owning side to null (unless already changed)
            if ($confVolet->getProjet() === $this) {
                $confVolet->setProjet(null);
            }
        }

        return $this;
    }

    public function getConfVolet(): ?ConfVolet
    {
        return $this->confVolet;
    }

    public function getConfVoletId(): ?int
    {
        return $this->confVolet?->getId();
    }

    public function setConfVolet(?ConfVolet $confVolet): static
    {
        $this->confVolet = $confVolet;
        return $this;
    }

    public function __toString(): string
    {
        return $this->refClient . ' - ' . ($this->description ?? 'Projet #' . $this->id);
    }
}