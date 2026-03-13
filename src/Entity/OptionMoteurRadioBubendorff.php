<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OptionMoteurRadioBubendorffRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionMoteurRadioBubendorffRepository::class)]
#[ORM\Table(name: 'Options_Moteur_Radio_Bubendorff')]
class OptionMoteurRadioBubendorff
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
