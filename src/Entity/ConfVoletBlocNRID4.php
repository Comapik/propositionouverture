<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConfVoletBlocNRID4Repository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfVoletBlocNRID4Repository::class)]
#[ORM\Table(name: 'Conf_volet_BLOC_N_R_iD4')]
class ConfVoletBlocNRID4
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'caisson_pvc_id', referencedColumnName: 'id', nullable: true)]
    private ?CaissonPvc $caissonPvc = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'tablier_id', referencedColumnName: 'id', nullable: true)]
    private ?Tablier $tablier = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teintes_tablier_volet_id', referencedColumnName: 'id', nullable: true)]
    private ?TeinteTablierVolet $teinteTablierVolet = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teintes_encadrement_volet_id', referencedColumnName: 'id', nullable: true)]
    private ?TeinteEncadrementVolet $teinteEncadrementVolet = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teintes_id', referencedColumnName: 'id', nullable: true)]
    private ?Teinte $teinte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'specificites_caisson_id', referencedColumnName: 'id', nullable: true)]
    private ?SpecificiteCaisson $specificiteCaisson = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'options_moteur_radio_bubendorff_id', referencedColumnName: 'id', nullable: true)]
    private ?OptionMoteurRadioBubendorff $optionMoteurRadioBubendorff = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'option_moteur_filaire_bubendorff_id', referencedColumnName: 'id', nullable: true)]
    private ?OptionMoteurFilaireBubendorff $optionMoteurFilaireBubendorff = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'option_pack_sav_id', referencedColumnName: 'id', nullable: true)]
    private ?OptionPackSav $optionPackSav = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'lignes_de_commande_bloc_n_r_id4_id', referencedColumnName: 'id', nullable: true)]
    private ?LigneDeCommandeBlocNRID4 $ligneDeCommandeBlocNRID4 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaissonPvc(): ?CaissonPvc
    {
        return $this->caissonPvc;
    }

    public function setCaissonPvc(?CaissonPvc $caissonPvc): static
    {
        $this->caissonPvc = $caissonPvc;

        return $this;
    }

    public function getTablier(): ?Tablier
    {
        return $this->tablier;
    }

    public function setTablier(?Tablier $tablier): static
    {
        $this->tablier = $tablier;

        return $this;
    }

    public function getTeinteTablierVolet(): ?TeinteTablierVolet
    {
        return $this->teinteTablierVolet;
    }

    public function setTeinteTablierVolet(?TeinteTablierVolet $teinteTablierVolet): static
    {
        $this->teinteTablierVolet = $teinteTablierVolet;

        return $this;
    }

    public function getTeinteEncadrementVolet(): ?TeinteEncadrementVolet
    {
        return $this->teinteEncadrementVolet;
    }

    public function setTeinteEncadrementVolet(?TeinteEncadrementVolet $teinteEncadrementVolet): static
    {
        $this->teinteEncadrementVolet = $teinteEncadrementVolet;

        return $this;
    }

    public function getTeinte(): ?Teinte
    {
        return $this->teinte;
    }

    public function setTeinte(?Teinte $teinte): static
    {
        $this->teinte = $teinte;

        return $this;
    }

    public function getSpecificiteCaisson(): ?SpecificiteCaisson
    {
        return $this->specificiteCaisson;
    }

    public function setSpecificiteCaisson(?SpecificiteCaisson $specificiteCaisson): static
    {
        $this->specificiteCaisson = $specificiteCaisson;

        return $this;
    }

    public function getOptionMoteurRadioBubendorff(): ?OptionMoteurRadioBubendorff
    {
        return $this->optionMoteurRadioBubendorff;
    }

    public function setOptionMoteurRadioBubendorff(?OptionMoteurRadioBubendorff $optionMoteurRadioBubendorff): static
    {
        $this->optionMoteurRadioBubendorff = $optionMoteurRadioBubendorff;

        return $this;
    }

    public function getOptionMoteurFilaireBubendorff(): ?OptionMoteurFilaireBubendorff
    {
        return $this->optionMoteurFilaireBubendorff;
    }

    public function setOptionMoteurFilaireBubendorff(?OptionMoteurFilaireBubendorff $optionMoteurFilaireBubendorff): static
    {
        $this->optionMoteurFilaireBubendorff = $optionMoteurFilaireBubendorff;

        return $this;
    }

    public function getOptionPackSav(): ?OptionPackSav
    {
        return $this->optionPackSav;
    }

    public function setOptionPackSav(?OptionPackSav $optionPackSav): static
    {
        $this->optionPackSav = $optionPackSav;

        return $this;
    }

    public function getLigneDeCommandeBlocNRID4(): ?LigneDeCommandeBlocNRID4
    {
        return $this->ligneDeCommandeBlocNRID4;
    }

    public function setLigneDeCommandeBlocNRID4(?LigneDeCommandeBlocNRID4 $ligneDeCommandeBlocNRID4): static
    {
        $this->ligneDeCommandeBlocNRID4 = $ligneDeCommandeBlocNRID4;

        return $this;
    }
}
