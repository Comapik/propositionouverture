<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Ouverture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-ouverture-sens',
    description: 'Met à jour le sens d\'ouverture des portes selon leur nom'
)]
class UpdateOuvertureSensCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Mise à jour du sens d\'ouverture des portes');

        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();
        $updated = 0;

        foreach ($ouvertures as $ouverture) {
            $nom = strtolower($ouverture->getNom());
            $sens = null;

            // Règles pour déterminer le sens d'ouverture selon le nom
            if (str_contains($nom, "vers l'extérieur") || str_contains($nom, 'ext')) {
                $sens = 'ext';
            } elseif (str_contains($nom, "vers l'intérieur") || str_contains($nom, 'int')) {
                $sens = 'int';
            } elseif (str_contains($nom, "porte d'entrée") || str_contains($nom, "porte entree")) {
                // Les portes d'entrée s'ouvrent généralement vers l'intérieur
                $sens = 'int';
            } elseif (str_contains($nom, 'porte de service')) {
                // Analyser plus finement pour les portes de service
                if (str_contains($nom, "vers l'extérieur")) {
                    $sens = 'ext';
                } else {
                    $sens = 'int';
                }
            } elseif (str_contains($nom, 'porte alu') || str_contains($nom, 'portes alu')) {
                // Pour les portes alu sans indication, on propose les deux sens
                // mais on peut partir du principe que c'est plutôt intérieur par défaut
                $sens = 'int';
            }

            if ($sens) {
                $ouverture->setSensOuverture($sens);
                $updated++;
                $io->text(sprintf('✓ %s → %s', $ouverture->getNom(), $sens === 'ext' ? 'extérieur' : 'intérieur'));
            } else {
                $io->text(sprintf('- %s → non défini', $ouverture->getNom()));
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d ouvertures mises à jour sur %d total', $updated, count($ouvertures)));

        return Command::SUCCESS;
    }
}