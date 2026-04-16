<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'teinte_encadrement')]
class TeinteEncadrement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teinte_encadrement_elargi_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TeinteEncadrementElargi $teinteEncadrementElargi = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teinte_encadrement_specifique_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TeinteEncadrementSpecifique $teinteEncadrementSpecifique = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'nuancier_standard_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?NuancierStandard $nuancierStandard = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeinteEncadrementElargi(): ?TeinteEncadrementElargi
    {
        return $this->teinteEncadrementElargi;
    }

    public function setTeinteEncadrementElargi(?TeinteEncadrementElargi $teinteEncadrementElargi): static
    {
        $this->teinteEncadrementElargi = $teinteEncadrementElargi;

        return $this;
    }

    public function getTeinteEncadrementSpecifique(): ?TeinteEncadrementSpecifique
    {
        return $this->teinteEncadrementSpecifique;
    }

    public function setTeinteEncadrementSpecifique(?TeinteEncadrementSpecifique $teinteEncadrementSpecifique): static
    {
        $this->teinteEncadrementSpecifique = $teinteEncadrementSpecifique;

        return $this;
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
}
