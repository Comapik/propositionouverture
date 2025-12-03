<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\TypeFenetrePorteCompatibilite;
use App\Repository\TypeFenetrePorteRepository;
use App\Repository\OuvertureRepository;
use App\Repository\SystemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour migrer les relations ManyToMany vers la table de compatibilité ternaire.
 */
#[AsCommand(
    name: 'app:migrate-type-fenetre-porte-compatibilite',
    description: 'Migre les relations ManyToMany existantes vers la table de compatibilité ternaire'
)]
class MigrateTypeFenetrePorteCompatibiliteCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeFenetrePorteRepository $typeFenetrePorteRepository,
        private readonly OuvertureRepository $ouvertureRepository,
        private readonly SystemeRepository $systemeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migration des compatibilités TypeFenetrePorte');

        // Récupérer tous les types de fenêtre/porte existants
        $typesFenetrePorte = $this->typeFenetrePorteRepository->findAll();
        
        if (empty($typesFenetrePorte)) {
            $io->warning('Aucun type de fenêtre/porte trouvé à migrer.');
            return Command::SUCCESS;
        }

        $totalCompatibilites = 0;
        $io->progressStart(count($typesFenetrePorte));

        foreach ($typesFenetrePorte as $type) {
            // Pour chaque type, créer des compatibilités pour toutes les combinaisons ouverture/système
            $ouvertures = $type->getOuvertures();
            $systemes = $type->getSystemes();

            foreach ($ouvertures as $ouverture) {
                foreach ($systemes as $systeme) {
                    // Vérifier si cette compatibilité existe déjà
                    $existingCompatibilite = $this->entityManager
                        ->getRepository(TypeFenetrePorteCompatibilite::class)
                        ->findOneBy([
                            'typeFenetrePorte' => $type,
                            'ouverture' => $ouverture,
                            'systeme' => $systeme,
                        ]);

                    if (!$existingCompatibilite) {
                        // Créer une nouvelle compatibilité
                        $compatibilite = new TypeFenetrePorteCompatibilite();
                        $compatibilite->setTypeFenetrePorte($type);
                        $compatibilite->setOuverture($ouverture);
                        $compatibilite->setSysteme($systeme);

                        $this->entityManager->persist($compatibilite);
                        $totalCompatibilites++;
                    }
                }
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf(
            'Migration terminée ! %d compatibilités créées pour %d types de fenêtre/porte.',
            $totalCompatibilites,
            count($typesFenetrePorte)
        ));

        // Afficher un résumé
        $io->section('Résumé des compatibilités créées :');
        $compatibilites = $this->entityManager
            ->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findAll();

        $groupedByType = [];
        foreach ($compatibilites as $compatibilite) {
            $typeName = $compatibilite->getTypeFenetrePorte()->getNom();
            if (!isset($groupedByType[$typeName])) {
                $groupedByType[$typeName] = 0;
            }
            $groupedByType[$typeName]++;
        }

        foreach ($groupedByType as $typeName => $count) {
            $io->text(sprintf('- %s : %d compatibilités', $typeName, $count));
        }

        return Command::SUCCESS;
    }
}