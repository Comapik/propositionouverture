<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OptionMoteurFilaireBubendorffRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionMoteurFilaireBubendorffRepository::class)]
#[ORM\Table(name: 'Option Moteur-Filaire_Bubendorff')]
class OptionMoteurFilaireBubendorff
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
