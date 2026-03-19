<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjetPdfRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetPdfRepository::class)]
#[ORM\Table(name: 'projet_pdf')]
class ProjetPdf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Projet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(targetEntity: ConfPf::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ConfPf $confPf = null;

    #[ORM\ManyToOne(targetEntity: PdfSchema::class, inversedBy: 'projetPdfs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PdfSchema $pdfSchema = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(length: 500)]
    private ?string $filePath = null;

    #[ORM\Column]
    private ?float $customValue = null;

    #[ORM\Column]
    private ?int $fileSize = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->type = 'plan_technique';
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

    public function getConfPf(): ?ConfPf
    {
        return $this->confPf;
    }

    public function setConfPf(?ConfPf $confPf): static
    {
        $this->confPf = $confPf;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getCustomValue(): ?float
    {
        return $this->customValue;
    }

    public function setCustomValue(float $customValue): static
    {
        $this->customValue = $customValue;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

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

    public function getPdfSchema(): ?PdfSchema
    {
        return $this->pdfSchema;
    }

    public function setPdfSchema(?PdfSchema $pdfSchema): static
    {
        $this->pdfSchema = $pdfSchema;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getEncodedPath(): string
    {
        return base64_encode($this->filePath);
    }

    public function getFormattedSize(): string
    {
        if ($this->fileSize < 1024) {
            return $this->fileSize . ' B';
        } elseif ($this->fileSize < 1024 * 1024) {
            return round($this->fileSize / 1024, 1) . ' KB';
        } else {
            return round($this->fileSize / (1024 * 1024), 1) . ' MB';
        }
    }
}