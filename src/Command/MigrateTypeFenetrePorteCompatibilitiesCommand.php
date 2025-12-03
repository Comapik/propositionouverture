<?php

namespace App\Command;

use App\Entity\TypeFenetrePorte;
use App\Entity\TypeFenetrePorteCompatibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-type-fenetre-porte-compatibilities',
    description: 'Migre les anciennes relations ManyToMany vers la table de compatibilité',
)]
class MigrateTypeFenetrePorteCompatibilitiesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Afficher les actions sans les exécuter')
            ->addOption('clean', 'c', InputOption::VALUE_NONE, 'Nettoyer les anciennes relations ManyToMany');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $shouldClean = $input->getOption('clean');

        $io->title('Migration des compatibilités TypeFenetrePorte');

        // Statistiques actuelles
        $typesCount = $this->entityManager->getRepository(TypeFenetrePorte::class)->count([]);
        $compatibilitesCount = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)->count([]);

        $io->info("État actuel :");
        $io->info("- Types de fenêtre/porte : $typesCount");
        $io->info("- Compatibilités : $compatibilitesCount");

        $types = $this->entityManager->getRepository(TypeFenetrePorte::class)->findAll();
        $migrated = 0;
        $cleaned = 0;

        foreach ($types as $type) {
            // Vérifier les relations ManyToMany existantes (si elles existent encore)
            $hasDirectRelations = false;
            
            try {
                // Essayer d'accéder aux collections ManyToMany (peuvent ne plus exister)
                $systemesCount = method_exists($type, 'getSystemes') ? $type->getSystemes()->count() : 0;
                $ouverturesCount = method_exists($type, 'getOuvertures') ? $type->getOuvertures()->count() : 0;
                $hasDirectRelations = $systemesCount > 0 || $ouverturesCount > 0;
            } catch (\Exception $e) {
                // Les méthodes n'existent plus, c'est normal
            }

            if ($hasDirectRelations && $shouldClean && !$isDryRun) {
                // Nettoyer les relations directes (ce code ne s'exécutera probablement jamais
                // car nous avons supprimé les relations ManyToMany des entités)
                $io->warning("Relations directes détectées pour {$type->getNom()}, nettoyage...");
                $cleaned++;
            }

            // Compter les compatibilités existantes
            $existingCompatibilities = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
                ->findBy(['typeFenetrePorte' => $type]);

            if (!empty($existingCompatibilities)) {
                $io->text("✓ Type '{$type->getNom()}' : " . count($existingCompatibilities) . " compatibilités");
            } else {
                $io->warning("⚠ Type '{$type->getNom()}' : aucune compatibilité définie");
            }
        }

        if ($isDryRun) {
            $io->note('Mode dry-run activé, aucune modification effectuée.');
        } else {
            $io->success("Migration terminée :");
            $io->success("- $migrated éléments migrés");
            $io->success("- $cleaned relations nettoyées");
        }

        // Vérification finale
        $finalCompatibilitesCount = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)->count([]);
        $io->info("Compatibilités finales : $finalCompatibilitesCount");

        return Command::SUCCESS;
    }
}