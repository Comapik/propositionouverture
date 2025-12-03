<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\TypeFenetrePorteCompatibiliteService;
use App\Repository\TypeFenetrePorteRepository;
use App\Repository\OuvertureRepository;
use App\Repository\SystemeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour vérifier et afficher les compatibilités configurées.
 */
#[AsCommand(
    name: 'app:check-compatibilites',
    description: 'Vérifie et affiche les compatibilités configurées entre types, ouvertures et systèmes'
)]
class CheckCompatibilitesCommand extends Command
{
    public function __construct(
        private readonly TypeFenetrePorteCompatibiliteService $compatibiliteService,
        private readonly TypeFenetrePorteRepository $typeRepository,
        private readonly OuvertureRepository $ouvertureRepository,
        private readonly SystemeRepository $systemeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Vérification des compatibilités TypeFenetrePorte');

        // Récupérer quelques exemples pour tester
        $ouvertures = $this->ouvertureRepository->findBy([], ['nom' => 'ASC'], 3);
        $systemes = $this->systemeRepository->findBy([], ['nom' => 'ASC'], 3);

        if (empty($ouvertures) || empty($systemes)) {
            $io->error('Aucune ouverture ou système trouvé pour les tests.');
            return Command::FAILURE;
        }

        $io->section('Test de compatibilités pour quelques combinaisons :');

        foreach ($ouvertures as $ouverture) {
            foreach ($systemes as $systeme) {
                $typesCompatibles = $this->compatibiliteService->getTypesCompatibles($ouverture, $systeme);
                
                $io->text(sprintf(
                    '📌 <info>%s</info> + <info>%s</info> = <comment>%d types compatibles</comment>',
                    $ouverture->getNom(),
                    $systeme->getNom(),
                    count($typesCompatibles)
                ));

                if (count($typesCompatibles) > 0) {
                    foreach ($typesCompatibles as $type) {
                        $io->text('   - ' . $type->getNom());
                    }
                } else {
                    $io->text('   ⚠️  Aucun type compatible trouvé');
                }
                
                $io->newLine();
            }
        }

        // Statistiques globales
        $io->section('Statistiques globales :');
        
        $totalTypes = count($this->typeRepository->findAll());
        $totalOuvertures = count($this->ouvertureRepository->findAll());
        $totalSystemes = count($this->systemeRepository->findAll());

        $io->text("📊 <info>Types de fenêtre/porte :</info> {$totalTypes}");
        $io->text("📊 <info>Ouvertures :</info> {$totalOuvertures}");
        $io->text("📊 <info>Systèmes :</info> {$totalSystemes}");

        // Compter les compatibilités par type
        $io->section('Compatibilités par type :');
        $types = $this->typeRepository->findAll();
        
        foreach ($types as $type) {
            $count = $this->compatibiliteService->countCompatibilites($type);
            $io->text(sprintf(
                '🔗 <info>%s :</info> %d compatibilités',
                $type->getNom(),
                $count
            ));
        }

        $io->success('Vérification terminée avec succès !');

        return Command::SUCCESS;
    }
}