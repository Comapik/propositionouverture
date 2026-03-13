<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LigneDeCommandeBlocNRID4Repository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneDeCommandeBlocNRID4Repository::class)]
#[ORM\Table(name: 'Lignes_de_commande_BLOC_N_R_iD4')]
class LigneDeCommandeBlocNRID4
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
