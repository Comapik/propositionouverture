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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-missing-type-fenetre-porte',
    description: 'Vérifie toutes les combinaisons système/ouverture sans types de fenêtre/porte compatibles'
)]
class CheckMissingTypeFenetrePorteCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeFenetrePorteCompatibiliteRepository $compatibiliteRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Vérification des types de fenêtre/porte manquants');

        // Récupérer tous les systèmes et ouvertures
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();

        $io->info(sprintf('Systèmes trouvés: %d', count($systemes)));
        $io->info(sprintf('Ouvertures trouvées: %d', count($ouvertures)));

        $problematicCombinations = [];
        $totalCombinations = 0;

        // Vérifier chaque combinaison
        foreach ($systemes as $systeme) {
            foreach ($ouvertures as $ouverture) {
                $totalCombinations++;
                $types = $this->compatibiliteRepository->findTypesFenetrePorteByOuvertureAndSysteme(
                    $ouverture,
                    $systeme
                );

                if (empty($types)) {
                    $fournisseur = $systeme->getFournisseur();
                    $systemeNom = $fournisseur 
                        ? $fournisseur->getMarque() . ' - ' . $systeme->getNom()
                        : $systeme->getNom();
                    
                    $problematicCombinations[] = [
                        'systeme_id' => $systeme->getId(),
                        'systeme_nom' => $systemeNom,
                        'ouverture_id' => $ouverture->getId(),
                        'ouverture_nom' => $ouverture->getNom(),
                    ];
                }
            }
        }

        $io->section('Résultats');
        $io->text(sprintf('Total de combinaisons testées: %d', $totalCombinations));
        $io->text(sprintf('Combinaisons problématiques: %d', count($problematicCombinations)));

        if (empty($problematicCombinations)) {
            $io->success('Toutes les combinaisons système/ouverture ont au moins un type de fenêtre/porte compatible !');
            return Command::SUCCESS;
        }

        // Grouper par système pour une meilleure lisibilité
        $groupedBySysteme = [];
        foreach ($problematicCombinations as $combo) {
            $systemeKey = $combo['systeme_id'] . ' - ' . $combo['systeme_nom'];
            if (!isset($groupedBySysteme[$systemeKey])) {
                $groupedBySysteme[$systemeKey] = [];
            }
            $groupedBySysteme[$systemeKey][] = $combo;
        }

        $io->section('Combinaisons système/ouverture sans types de fenêtre/porte');
        
        foreach ($groupedBySysteme as $systemeKey => $combos) {
            $io->warning(sprintf('Système: %s (%d ouvertures problématiques)', 
                $systemeKey, 
                count($combos)
            ));
            
            $rows = [];
            foreach ($combos as $combo) {
                $rows[] = [
                    $combo['ouverture_id'],
                    $combo['ouverture_nom'],
                ];
            }
            
            $io->table(
                ['ID Ouverture', 'Nom Ouverture'],
                $rows
            );
        }

        // Statistiques par système
        $io->section('Statistiques par système');
        $systemStats = [];
        foreach ($groupedBySysteme as $systemeKey => $combos) {
            $systemStats[] = [$systemeKey, count($combos)];
        }
        
        // Trier par nombre de problèmes décroissant
        usort($systemStats, function($a, $b) {
            return $b[1] <=> $a[1];
        });
        
        $io->table(
            ['Système', 'Ouvertures sans types'],
            $systemStats
        );

        $io->note(sprintf(
            'Pour résoudre ces problèmes, utilisez l\'interface de gestion des types de fenêtre/porte à: %s',
            '/temp/type-fenetre-porte'
        ));

        return Command::SUCCESS;
    }
}
