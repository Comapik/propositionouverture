<?php

namespace App\Command;

use App\Entity\Systeme;
use App\Entity\Ouverture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'app:associate-systeme-ouverture',
    description: 'Associate systemes with ouvertures using the new Many-to-Many relationship',
)]
class AssociateSystemeOuvertureCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('systeme-id', InputArgument::OPTIONAL, 'ID du système à associer')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Lister tous les systèmes et leurs ouvertures')
            ->addOption('auto', 'a', InputOption::VALUE_NONE, 'Association automatique basée sur les données existantes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listAssociations($io);
        }

        if ($input->getOption('auto')) {
            return $this->autoAssociate($io);
        }

        $systemeId = $input->getArgument('systeme-id');
        if ($systemeId) {
            return $this->associateSpecificSysteme($io, (int) $systemeId);
        }

        return $this->interactiveAssociation($io);
    }

    private function listAssociations(SymfonyStyle $io): int
    {
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        
        $io->title('Associations Système → Ouvertures');
        
        foreach ($systemes as $systeme) {
            $ouverturesNames = [];
            foreach ($systeme->getOuvertures() as $ouverture) {
                $ouverturesNames[] = $ouverture->getNom();
            }
            
            $io->writeln(sprintf(
                '<info>%s</info> (ID: %d, Fournisseur: %s) → <comment>%s</comment>',
                $systeme->getNom(),
                $systeme->getId(),
                $systeme->getFournisseur()->getMarque(),
                empty($ouverturesNames) ? 'Aucune ouverture associée' : implode(', ', $ouverturesNames)
            ));
        }
        
        return Command::SUCCESS;
    }

    private function autoAssociate(SymfonyStyle $io): int
    {
        $io->title('Association automatique Système → Ouvertures');
        $io->note('Cette fonction permet d\'associer automatiquement tous les systèmes avec toutes les ouvertures de leur catégorie.');
        
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();
        
        $io->progressStart(count($systemes));
        
        $associationsCount = 0;
        foreach ($systemes as $systeme) {
            foreach ($ouvertures as $ouverture) {
                if (!$systeme->getOuvertures()->contains($ouverture)) {
                    $systeme->addOuverture($ouverture);
                    $associationsCount++;
                }
            }
            $io->progressAdvance();
        }
        
        $this->entityManager->flush();
        $io->progressFinish();
        
        $io->success(sprintf('Associations automatiques créées : %d', $associationsCount));
        
        return Command::SUCCESS;
    }

    private function interactiveAssociation(SymfonyStyle $io): int
    {
        $io->title('Association interactive Système → Ouvertures');
        
        // Sélection du système
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $systemeChoices = [];
        foreach ($systemes as $systeme) {
            $systemeChoices[$systeme->getId()] = sprintf(
                '%s (ID: %d, Fournisseur: %s)',
                $systeme->getNom(),
                $systeme->getId(),
                $systeme->getFournisseur()->getMarque()
            );
        }
        
        $systemeId = $io->choice('Sélectionnez un système', $systemeChoices);
        $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
        
        if (!$systeme) {
            $io->error('Système non trouvé');
            return Command::FAILURE;
        }
        
        return $this->associateSpecificSysteme($io, $systeme->getId());
    }

    private function associateSpecificSysteme(SymfonyStyle $io, int $systemeId): int
    {
        $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
        
        if (!$systeme) {
            $io->error(sprintf('Système avec l\'ID %d non trouvé', $systemeId));
            return Command::FAILURE;
        }
        
        $io->title(sprintf('Association d\'ouvertures pour le système : %s', $systeme->getNom()));
        
        // Afficher les ouvertures déjà associées
        $currentOuvertures = $systeme->getOuvertures();
        if (!$currentOuvertures->isEmpty()) {
            $io->section('Ouvertures actuellement associées :');
            foreach ($currentOuvertures as $ouverture) {
                $io->writeln('- ' . $ouverture->getNom());
            }
        }
        
        // Sélection des nouvelles ouvertures
        $allOuvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();
        $ouvertureChoices = [];
        foreach ($allOuvertures as $ouverture) {
            if (!$currentOuvertures->contains($ouverture)) {
                $ouvertureChoices[] = $ouverture->getNom();
            }
        }
        
        if (empty($ouvertureChoices)) {
            $io->info('Toutes les ouvertures sont déjà associées à ce système.');
            return Command::SUCCESS;
        }
        
        $selectedOuvertures = $io->choice(
            'Sélectionnez les ouvertures à associer (séparées par des virgules)',
            $ouvertureChoices,
            null,
            true
        );
        
        // Association des ouvertures
        $associationsCount = 0;
        foreach ($selectedOuvertures as $ouvertureNom) {
            $ouverture = $this->entityManager->getRepository(Ouverture::class)
                ->findOneBy(['nom' => $ouvertureNom]);
            
            if ($ouverture && !$systeme->getOuvertures()->contains($ouverture)) {
                $systeme->addOuverture($ouverture);
                $associationsCount++;
                $io->writeln(sprintf('<info>✓</info> Associé avec : %s', $ouverture->getNom()));
            }
        }
        
        $this->entityManager->flush();
        
        $io->success(sprintf('Nombre d\'associations créées : %d', $associationsCount));
        
        return Command::SUCCESS;
    }
}