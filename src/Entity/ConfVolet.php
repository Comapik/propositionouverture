<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConfVoletRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ConfVolet entity following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Represents volet configuration data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized volet configuration management
 * Following KISS principle: Simple configuration structure
 */
#[ORM\Entity(repositoryClass: ConfVoletRepository::class)]
#[ORM\Table(
    name: 'conf_volet',
    indexes: [
        new ORM\Index(name: 'idx_conf_volet_caisson_pvc', columns: ['caisson_pvc_id']),
        new ORM\Index(name: 'idx_conf_volet_tablier', columns: ['tablier_id']),
        new ORM\Index(name: 'idx_conf_volet_teinte_elargi', columns: ['teinte_encadrement_elargi_id']),
        new ORM\Index(name: 'idx_conf_volet_teinte_specifique', columns: ['teinte_encadrement_specifique_id']),
        new ORM\Index(name: 'idx_conf_volet_nuancier_encadrement', columns: ['nuancier_standard_encadrement_id']),
        new ORM\Index(name: 'idx_conf_volet_pack_sav', columns: ['option_pack_sav_id']),
    ]
)]
#[ORM\HasLifecycleCallbacks]
class ConfVolet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'confVolets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?GammeVolet $gammeVolet = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'caisson_pvc_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CaissonPvc $caissonPvc = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'tablier_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Tablier $tablier = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teinte_encadrement_elargi_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TeinteEncadrementElargi $teinteEncadrementElargi = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'teinte_encadrement_specifique_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TeinteEncadrementSpecifique $teinteEncadrementSpecifique = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'nuancier_standard_encadrement_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?NuancierStandard $nuancierStandardEncadrement = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'option_pack_sav_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?OptionPackSav $optionPackSav = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'option_autre_teinte', length: 255, nullable: true)]
    private ?string $optionAutreTeinte = null;

    #[ORM\Column(name: 'cmg_groupe_climat_plus', nullable: true)]
    private ?int $cmgGroupeClimatPlus = null;

    #[ORM\Column(name: 'Extension_offre', type: 'boolean', nullable: true)]
    private ?bool $extensionOffre = null;

    #[ORM\Column(name: 'face_exterieure_alu', type: 'boolean', nullable: true)]
    private ?bool $faceExterieureAlu = null;

    #[ORM\Column(name: 'pht_n', type: 'boolean', nullable: true)]
    private ?bool $phtN = null;

    #[ORM\Column(name: 'pht_r', type: 'boolean', nullable: true)]
    private ?bool $phtR = null;

    #[ORM\Column(name: 'h4c_horloge_4_canaux', type: 'boolean', nullable: true)]
    private ?bool $h4cHorloge4Canaux = null;

    #[ORM\Column(name: 'dia_idiamant', type: 'boolean', nullable: true)]
    private ?bool $diaIdiamant = null;

    #[ORM\Column(name: 'smu_support_mural_3_boutons', type: 'boolean', nullable: true)]
    private ?bool $smuSupportMural3Boutons = null;

    #[ORM\Column(name: 'inv_avec_inverseur', type: 'boolean', nullable: true)]
    private ?bool $invAvecInverseur = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): static
    {
        $this->projet = $projet;
        return $this;
    }

    public function getGammeVolet(): ?GammeVolet
    {
        return $this->gammeVolet;
    }

    public function setGammeVolet(?GammeVolet $gammeVolet): static
    {
        $this->gammeVolet = $gammeVolet;
        return $this;
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

    public function getNuancierStandardEncadrement(): ?NuancierStandard
    {
        return $this->nuancierStandardEncadrement;
    }

    public function setNuancierStandardEncadrement(?NuancierStandard $nuancierStandardEncadrement): static
    {
        $this->nuancierStandardEncadrement = $nuancierStandardEncadrement;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getOptionAutreTeinte(): ?string
    {
        return $this->optionAutreTeinte;
    }

    public function setOptionAutreTeinte(?string $optionAutreTeinte): static
    {
        $this->optionAutreTeinte = $optionAutreTeinte;
        return $this;
    }

    public function getCmgGroupeClimatPlus(): ?int
    {
        return $this->cmgGroupeClimatPlus;
    }

    public function setCmgGroupeClimatPlus(?int $cmgGroupeClimatPlus): static
    {
        $this->cmgGroupeClimatPlus = $cmgGroupeClimatPlus;
        return $this;
    }

    public function getExtensionOffre(): ?bool
    {
        return $this->extensionOffre;
    }

    public function setExtensionOffre(?bool $extensionOffre): static
    {
        $this->extensionOffre = $extensionOffre;
        return $this;
    }

    public function getFaceExterieureAlu(): ?bool
    {
        return $this->faceExterieureAlu;
    }

    public function setFaceExterieureAlu(?bool $faceExterieureAlu): static
    {
        $this->faceExterieureAlu = $faceExterieureAlu;
        return $this;
    }

    public function getPhtN(): ?bool
    {
        return $this->phtN;
    }

    public function setPhtN(?bool $phtN): static
    {
        $this->phtN = $phtN;
        return $this;
    }

    public function getPhtR(): ?bool
    {
        return $this->phtR;
    }

    public function setPhtR(?bool $phtR): static
    {
        $this->phtR = $phtR;
        return $this;
    }

    public function getH4cHorloge4Canaux(): ?bool
    {
        return $this->h4cHorloge4Canaux;
    }

    public function setH4cHorloge4Canaux(?bool $h4cHorloge4Canaux): static
    {
        $this->h4cHorloge4Canaux = $h4cHorloge4Canaux;
        return $this;
    }

    public function getDiaIdiamant(): ?bool
    {
        return $this->diaIdiamant;
    }

    public function setDiaIdiamant(?bool $diaIdiamant): static
    {
        $this->diaIdiamant = $diaIdiamant;
        return $this;
    }

    public function getSmuSupportMural3Boutons(): ?bool
    {
        return $this->smuSupportMural3Boutons;
    }

    public function setSmuSupportMural3Boutons(?bool $smuSupportMural3Boutons): static
    {
        $this->smuSupportMural3Boutons = $smuSupportMural3Boutons;
        return $this;
    }

    public function getInvAvecInverseur(): ?bool
    {
        return $this->invAvecInverseur;
    }

    public function setInvAvecInverseur(?bool $invAvecInverseur): static
    {
        $this->invAvecInverseur = $invAvecInverseur;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Configuration Volet #' . $this->id;
    }
}
