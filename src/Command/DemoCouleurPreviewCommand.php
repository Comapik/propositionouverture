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
    name: 'app:demo:couleur-preview',
    description: 'Affiche un aperçu des couleurs RAL avec leurs codes hexadécimaux',
)]
class DemoCouleurPreviewCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Aperçu des couleurs RAL avec codes hexadécimaux');

        // Récupérer les premières couleurs RAL
        $couleursRAL = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.nom LIKE :pattern')
            ->andWhere('c.plaxageLaquageId = 1')
            ->andWhere('c.codeHex IS NOT NULL')
            ->setParameter('pattern', 'RAL %')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        if (empty($couleursRAL)) {
            $io->error('Aucune couleur RAL avec code hexadécimal trouvée');
            return Command::FAILURE;
        }

        $io->section(sprintf('Aperçu des %d premières couleurs RAL', count($couleursRAL)));

        $rows = [];
        foreach ($couleursRAL as $couleur) {
            $rows[] = [
                $couleur->getId(),
                $couleur->getNom(),
                $couleur->getCodeHex(),
                $couleur->getPlaxageLaquageId(),
                'RAL'
            ];
        }

        $io->table(
            ['ID', 'Nom', 'Code Hex', 'Plaxage ID', 'Type'],
            $rows
        );

        // Récupérer aussi quelques couleurs Renolit s'il y en a
        $couleursRenolit = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.plaxageLaquageId = 2')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        if (!empty($couleursRenolit)) {
            $io->section('Couleurs Renolit disponibles');
            $rows = [];
            foreach ($couleursRenolit as $couleur) {
                $rows[] = [
                    $couleur->getId(),
                    $couleur->getNom(),
                    $couleur->getUrlImage() ?? 'N/A',
                    $couleur->getPlaxageLaquageId(),
                    'Renolit'
                ];
            }

            $io->table(
                ['ID', 'Nom', 'URL Image', 'Plaxage ID', 'Type'],
                $rows
            );
        }

        // Informations de test
        $projets = $this->entityManager->getRepository(Projet::class)->findAll();
        $confPfs = $this->entityManager->getRepository(ConfPf::class)->findAll();

        if (!empty($projets) && !empty($confPfs)) {
            $premierProjet = $projets[0];
            $premiereConf = $confPfs[0];
            
            $io->success('Interface de couleurs prête !');
            $io->note(sprintf(
                'Pour tester l\'interface avec aperçu des couleurs RAL, accédez à: /configuration/pf/%d/couleurs/%d',
                $premierProjet->getId(),
                $premiereConf->getId()
            ));
            
            $io->text('L\'interface propose maintenant :');
            $io->text('• Grille visuelle des couleurs RAL avec aperçu hexadécimal');
            $io->text('• Grille des couleurs Renolit avec images');
            $io->text('• Boutons pour basculer entre RAL et Renolit');
            $io->text('• Aperçu en temps réel de la couleur sélectionnée');
        } else {
            $io->warning('Aucun projet ou configuration trouvé pour tester l\'interface');
        }

        return Command::SUCCESS;
    }
}