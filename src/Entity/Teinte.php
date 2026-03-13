<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeinteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeinteRepository::class)]
#[ORM\Table(name: 'Teintes')]
class Teinte
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
