<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'conf_teinte_tablier')]
class ConfTeinteTablier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'nuancier_standard_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?NuancierStandard $nuancierStandard = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'conf_volet_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ConfVolet $confVolet = null;

    #[ORM\Column(name: 'Tablier_faible_emissivite', type: 'boolean')]
    private bool $tablierFaibleEmissivite = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNuancierStandard(): ?NuancierStandard
    {
        return $this->nuancierStandard;
    }

    public function setNuancierStandard(?NuancierStandard $nuancierStandard): static
    {
        $this->nuancierStandard = $nuancierStandard;

        return $this;
    }

    public function getConfVolet(): ?ConfVolet
    {
        return $this->confVolet;
    }

    public function setConfVolet(?ConfVolet $confVolet): static
    {
        $this->confVolet = $confVolet;

        return $this;
    }

    public function isTablierFaibleEmissivite(): bool
    {
        return $this->tablierFaibleEmissivite;
    }

    public function setTablierFaibleEmissivite(bool $tablierFaibleEmissivite): static
    {
        $this->tablierFaibleEmissivite = $tablierFaibleEmissivite;

        return $this;
    }
}
