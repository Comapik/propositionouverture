<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Couleur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:couleur:list',
    description: 'Liste toutes les couleurs disponibles',
)]
class CouleurListCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $couleurs = $this->entityManager->getRepository(Couleur::class)->findAll();

        if (empty($couleurs)) {
            $io->warning('Aucune couleur trouvée dans la base de données.');
            return Command::SUCCESS;
        }

        $io->title('Liste des couleurs disponibles');

        $rows = [];
        foreach ($couleurs as $couleur) {
            $rows[] = [
                $couleur->getId(),
                $couleur->getNom(),
                $couleur->getPlaxageLaquageId() ?? 'N/A',
                $couleur->isHexColor() ? 'RAL' : 'Renolit',
                $couleur->getColorRepresentation() ?? 'N/A',
            ];
        }

        $io->table(
            ['ID', 'Nom', 'Plaxage/Laquage ID', 'Type', 'Représentation'],
            $rows
        );

        $io->success(sprintf('Total: %d couleurs', count($couleurs)));

        return Command::SUCCESS;
    }
}