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
    name: 'app:couleur:activate',
    description: 'Active/désactive les couleurs en masse',
)]
class CouleurActivateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('pattern', 'p', InputOption::VALUE_OPTIONAL, 'Motif pour filtrer les couleurs (ex: RAL)', 'RAL')
            ->addOption('activate', 'a', InputOption::VALUE_NONE, 'Activer les couleurs (par défaut)')
            ->addOption('deactivate', 'd', InputOption::VALUE_NONE, 'Désactiver les couleurs')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulation sans modification')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $pattern = $input->getOption('pattern');
        $activate = !$input->getOption('deactivate'); // Par défaut on active
        $dryRun = $input->getOption('dry-run');
        
        $action = $activate ? 'activation' : 'désactivation';
        
        $io->title(sprintf('%s des couleurs contenant "%s"', ucfirst($action), $pattern));

        // Récupérer les couleurs correspondant au motif
        $qb = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.nom LIKE :pattern')
            ->setParameter('pattern', '%' . $pattern . '%');

        if ($activate) {
            $qb->andWhere('c.actif = false');
        } else {
            $qb->andWhere('c.actif = true');
        }

        $couleurs = $qb->getQuery()->getResult();

        if (empty($couleurs)) {
            $io->warning(sprintf('Aucune couleur trouvée pour %s avec le motif "%s"', $action, $pattern));
            return Command::SUCCESS;
        }

        $io->section(sprintf('Couleurs trouvées : %d', count($couleurs)));

        // Afficher un échantillon des couleurs qui seront modifiées
        $sample = array_slice($couleurs, 0, 10);
        $rows = [];
        foreach ($sample as $couleur) {
            $rows[] = [
                $couleur->getId(),
                $couleur->getNom(),
                $couleur->isActif() ? 'Oui' : 'Non',
                $activate ? 'Oui' : 'Non'
            ];
        }

        $io->table(['ID', 'Nom', 'Actuel', 'Nouveau'], $rows);

        if (count($couleurs) > 10) {
            $io->text(sprintf('... et %d autres couleurs', count($couleurs) - 10));
        }

        if ($dryRun) {
            $io->note('Mode simulation - aucune modification effectuée');
            return Command::SUCCESS;
        }

        if (!$io->confirm(sprintf('Confirmer %s de %d couleurs ?', $action, count($couleurs)))) {
            $io->info('Opération annulée');
            return Command::SUCCESS;
        }

        // Effectuer la modification
        $processed = 0;
        foreach ($couleurs as $couleur) {
            $couleur->setActif($activate);
            $processed++;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%s de %d couleurs terminée avec succès !',
            ucfirst($action),
            $processed
        ));

        // Afficher les statistiques
        $totalActives = $this->entityManager->getRepository(Couleur::class)
            ->count(['actif' => true]);
        $totalInactives = $this->entityManager->getRepository(Couleur::class)
            ->count(['actif' => false]);

        $io->section('Statistiques');
        $io->text(sprintf('Couleurs actives : %d', $totalActives));
        $io->text(sprintf('Couleurs inactives : %d', $totalInactives));

        return Command::SUCCESS;
    }
}