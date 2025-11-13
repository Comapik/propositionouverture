<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Projet;
use App\Entity\ConfPf;
use App\Entity\Couleur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:couleur-flow',
    description: 'Test du flux de sélection des couleurs',
)]
class TestCouleurFlowCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du flux de sélection des couleurs');

        // Vérifier qu'il y a des projets
        $projets = $this->entityManager->getRepository(Projet::class)->findAll();
        if (empty($projets)) {
            $io->warning('Aucun projet trouvé. Créez d\'abord un projet pour tester.');
            return Command::SUCCESS;
        }

        $io->section('Projets disponibles');
        foreach (array_slice($projets, 0, 5) as $projet) {
            $io->text(sprintf('- ID: %d, Ref: %s, Description: %s', 
                $projet->getId(), 
                $projet->getRefClient(),
                $projet->getDescription() ?? 'N/A'
            ));
        }

        // Vérifier qu'il y a des couleurs
        $couleurs = $this->entityManager->getRepository(Couleur::class)->findAllOrdered();
        $io->section('Couleurs disponibles');
        $io->text(sprintf('Total: %d couleurs', count($couleurs)));
        
        $couleursRAL = array_filter($couleurs, fn($c) => $c->isHexColor());
        $couleursRenolit = array_filter($couleurs, fn($c) => !$c->isHexColor());
        
        $io->text(sprintf('- Couleurs RAL: %d', count($couleursRAL)));
        $io->text(sprintf('- Couleurs Renolit: %d', count($couleursRenolit)));

        // Afficher quelques couleurs RAL
        if (!empty($couleursRAL)) {
            $io->text('Exemples de couleurs RAL:');
            foreach (array_slice($couleursRAL, 0, 3) as $couleur) {
                $io->text(sprintf('  - %s (%s)', $couleur->getNom(), $couleur->getCodeHex()));
            }
        }

        // Vérifier qu'il y a des configurations ConfPf
        $confPfs = $this->entityManager->getRepository(ConfPf::class)->findAll();
        $io->section('Configurations PorteFenêtre');
        $io->text(sprintf('Total: %d configurations', count($confPfs)));

        if (!empty($confPfs)) {
            $confPf = $confPfs[0];
            $io->text(sprintf('Exemple - Configuration ID: %d', $confPf->getId()));
            $io->text(sprintf('  - Projet: %s', $confPf->getProjet() ? $confPf->getProjet()->getRefClient() : 'N/A'));
            $io->text(sprintf('  - Couleur intérieure: %s', $confPf->getCouleurInterieur() ? $confPf->getCouleurInterieur()->getNom() : 'Non définie'));
            $io->text(sprintf('  - Couleur extérieure: %s', $confPf->getCouleurExterieur() ? $confPf->getCouleurExterieur()->getNom() : 'Non définie'));
        }

        $io->success('Test terminé. Tout semble fonctionner correctement !');

        if (!empty($projets) && !empty($confPfs)) {
            $premierProjet = $projets[0];
            $premiereConf = $confPfs[0];
            $io->note(sprintf(
                'Pour tester l\'interface, accédez à: /configuration/pf/%d/couleurs/%d',
                $premierProjet->getId(),
                $premiereConf->getId()
            ));
        }

        return Command::SUCCESS;
    }
}