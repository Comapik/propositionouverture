<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Ouverture;
use App\Entity\Systeme;
use App\Repository\TypeFenetrePorteCompatibiliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export-missing-type-fenetre-porte',
    description: 'Exporte les combinaisons système/ouverture sans types de fenêtre/porte en CSV'
)]
class ExportMissingTypeFenetrePorteCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeFenetrePorteCompatibiliteRepository $compatibiliteRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'output',
            'o',
            InputOption::VALUE_OPTIONAL,
            'Chemin du fichier de sortie CSV',
            'missing_types_fenetre_porte.csv'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputFile = $input->getOption('output');

        $io->title('Export des types de fenêtre/porte manquants');

        // Récupérer tous les systèmes et ouvertures
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();

        $problematicCombinations = [];

        // Vérifier chaque combinaison
        $io->progressStart(count($systemes) * count($ouvertures));
        foreach ($systemes as $systeme) {
            foreach ($ouvertures as $ouverture) {
                $types = $this->compatibiliteRepository->findTypesFenetrePorteByOuvertureAndSysteme(
                    $ouverture,
                    $systeme
                );

                if (empty($types)) {
                    $fournisseur = $systeme->getFournisseur();
                    $problematicCombinations[] = [
                        'systeme_id' => $systeme->getId(),
                        'systeme_nom' => $systeme->getNom(),
                        'fournisseur' => $fournisseur ? $fournisseur->getMarque() : 'N/A',
                        'ouverture_id' => $ouverture->getId(),
                        'ouverture_nom' => $ouverture->getNom(),
                    ];
                }
                $io->progressAdvance();
            }
        }
        $io->progressFinish();

        // Écrire dans le fichier CSV
        $handle = fopen($outputFile, 'w');
        if (!$handle) {
            $io->error("Impossible d'ouvrir le fichier {$outputFile}");
            return Command::FAILURE;
        }

        // En-têtes
        fputcsv($handle, ['Système ID', 'Système', 'Fournisseur', 'Ouverture ID', 'Ouverture']);

        // Données
        foreach ($problematicCombinations as $combo) {
            fputcsv($handle, [
                $combo['systeme_id'],
                $combo['systeme_nom'],
                $combo['fournisseur'],
                $combo['ouverture_id'],
                $combo['ouverture_nom'],
            ]);
        }

        fclose($handle);

        $io->success([
            sprintf('Export terminé : %d combinaisons problématiques exportées', count($problematicCombinations)),
            sprintf('Fichier : %s', realpath($outputFile)),
        ]);

        return Command::SUCCESS;
    }
}
