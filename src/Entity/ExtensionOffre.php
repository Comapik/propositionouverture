<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExtensionOffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtensionOffreRepository::class)]
#[ORM\Table(name: 'extension_offre')]
class ExtensionOffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $exo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExo(): ?bool
    {
        return $this->exo;
    }

    public function setExo(bool $exo): static
    {
        $this->exo = $exo;

        return $this;
    }
}
