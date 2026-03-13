<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OptionPackSavRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionPackSavRepository::class)]
#[ORM\Table(name: 'Option_pack_SAV')]
class OptionPackSav
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
