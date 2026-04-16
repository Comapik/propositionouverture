<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'Lignes_de_commande_BLOC_N_R_iD4')]
class LigneCommandeBlocNrId4
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'type_coulisse_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TypeCoulisse $typeCoulisse = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'conf_volet_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ConfVolet $confVolet = null;

    #[ORM\Column(name: 'Nbre', nullable: true)]
    private ?int $nbre = null;

    #[ORM\Column(name: 'Largeur_(LA)', nullable: true)]
    private ?int $largeurLa = null;

    #[ORM\Column(name: 'Hauteur_(HC)', nullable: true)]
    private ?int $hauteurHc = null;

    #[ORM\Column(name: 'AT', nullable: true)]
    private ?int $at = null;

    #[ORM\Column(name: 'B1', nullable: true)]
    private ?int $b1 = null;

    #[ORM\Column(name: 'B2', nullable: true)]
    private ?int $b2 = null;

    #[ORM\Column(name: 'S1', nullable: true)]
    private ?int $s1 = null;

    #[ORM\Column(name: 'S2', nullable: true)]
    private ?int $s2 = null;

    #[ORM\Column(name: 'Repere', length: 255, nullable: true)]
    private ?string $repere = null;

    #[ORM\Column(name: 'Angle', nullable: true)]
    private ?int $angle = null;

    #[ORM\Column(name: 'Elargisseur_coulisse', type: 'boolean', nullable: true)]
    private ?bool $elargisseurCoulisse = null;

    #[ORM\Column(name: 'Câble_longueur_utile_5m', type: 'boolean', nullable: true)]
    private ?bool $cableLongueurUtile5m = null;

    #[ORM\Column(name: 'Panneau_PV_deporte', type: 'boolean', nullable: true)]
    private ?bool $panneauPvDeporte = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
