<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeinteEncadrementVoletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeinteEncadrementVoletRepository::class)]
#[ORM\Table(name: 'Teintes_encadrement_volet')]
class TeinteEncadrementVolet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
