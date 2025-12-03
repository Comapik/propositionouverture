<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TypeFenetrePorteCompatibiliteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité de liaison ternaire pour définir les compatibilités entre
 * TypeFenetrePorte, Ouverture et Systeme.
 * 
 * Cette entité modélise le fait qu'un type de fenêtre/porte est disponible
 * uniquement pour certaines combinaisons d'ouverture ET de système.
 */
#[ORM\Entity(repositoryClass: TypeFenetrePorteCompatibiliteRepository::class)]
#[ORM\Table(name: 'type_fenetre_porte_compatibilite')]
#[ORM\UniqueConstraint(
    name: 'type_ouverture_systeme_unique',
    columns: ['type_fenetre_porte_id', 'ouverture_id', 'systeme_id']
)]
class TypeFenetrePorteCompatibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'compatibilites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeFenetrePorte $typeFenetrePorte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ouverture $ouverture = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Systeme $systeme = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOuverture(): ?Ouverture
    {
        return $this->ouverture;
    }

    public function setOuverture(?Ouverture $ouverture): static
    {
        $this->ouverture = $ouverture;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}