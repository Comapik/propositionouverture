<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Couleur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:couleur:configure-ral',
    description: 'Configure les couleurs RAL avec plaxage_laquage_id = 1',
)]
class ConfigureRalCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulation sans modification')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $dryRun = $input->getOption('dry-run');
        
        $io->title('Configuration des couleurs RAL');

        // Récupérer toutes les couleurs qui commencent par "RAL"
        $qb = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.nom LIKE :pattern')
            ->setParameter('pattern', 'RAL %');

        $couleursRAL = $qb->getQuery()->getResult();

        if (empty($couleursRAL)) {
            $io->warning('Aucune couleur RAL trouvée');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Couleurs RAL trouvées : %d', count($couleursRAL)));

        // Analyser l'état actuel
        $aConfigurer = [];
        $dejaConfigureesCorrectement = 0;

        foreach ($couleursRAL as $couleur) {
            if ($couleur->getPlaxageLaquageId() !== 1) {
                $aConfigurer[] = $couleur;
            } else {
                $dejaConfigureesCorrectement++;
            }
        }

        $io->text(sprintf('Déjà configurées correctement : %d', $dejaConfigureesCorrectement));
        $io->text(sprintf('À configurer : %d', count($aConfigurer)));

        if (empty($aConfigurer)) {
            $io->success('Toutes les couleurs RAL sont déjà configurées correctement !');
            return Command::SUCCESS;
        }

        // Afficher un échantillon
        $sample = array_slice($aConfigurer, 0, 10);
        $rows = [];
        foreach ($sample as $couleur) {
            $rows[] = [
                $couleur->getId(),
                $couleur->getNom(),
                $couleur->getPlaxageLaquageId() ?? 'NULL',
                '1'
            ];
        }

        $io->table(['ID', 'Nom', 'Plaxage actuel', 'Nouveau plaxage'], $rows);

        if (count($aConfigurer) > 10) {
            $io->text(sprintf('... et %d autres couleurs', count($aConfigurer) - 10));
        }

        if ($dryRun) {
            $io->note('Mode simulation - aucune modification effectuée');
            return Command::SUCCESS;
        }

        if (!$io->confirm(sprintf('Configurer %d couleurs RAL avec plaxage_laquage_id = 1 ?', count($aConfigurer)))) {
            $io->info('Opération annulée');
            return Command::SUCCESS;
        }

        // Effectuer la configuration
        $processed = 0;
        foreach ($aConfigurer as $couleur) {
            $couleur->setPlaxageLaquageId(1);
            $processed++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Configuration de %d couleurs RAL terminée avec succès !', $processed));

        // Statistiques finales
        $totalRAL = count($couleursRAL);
        $totalConfigureesRAL = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.nom LIKE :pattern')
            ->andWhere('c.plaxageLaquageId = 1')
            ->setParameter('pattern', 'RAL %')
            ->getQuery()
            ->getSingleScalarResult();

        $io->section('Statistiques finales');
        $io->text(sprintf('Total couleurs RAL : %d', $totalRAL));
        $io->text(sprintf('Couleurs RAL configurées (plaxage = 1) : %d', $totalConfigureesRAL));

        return Command::SUCCESS;
    }
}