<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Client;
use App\Entity\ConfPf;
use App\Entity\Couleur;
use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test:configuration',
    description: 'Test la configuration des couleurs avec des données de test'
)]
class TestConfigurationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            // Vérifier les couleurs existantes
            $couleurs = $this->entityManager->getRepository(Couleur::class)->findAll();
            $io->success(sprintf('Couleurs trouvées: %d', count($couleurs)));
            
            // Afficher quelques couleurs RAL
            $couleursRAL = $this->entityManager->getRepository(Couleur::class)->findBy(['plaxageLaquageId' => 1], null, 5);
            $io->section('Couleurs RAL (échantillon):');
            foreach ($couleursRAL as $couleur) {
                $io->writeln(sprintf('- %s (ID: %d, Hex: %s)', 
                    $couleur->getNom(), 
                    $couleur->getId(), 
                    $couleur->getCodeHex()
                ));
            }
            
            // Afficher quelques couleurs Renolit
            $couleursRenolit = $this->entityManager->getRepository(Couleur::class)->findBy(['plaxageLaquageId' => 2], null, 5);
            $io->section('Couleurs Renolit (échantillon):');
            foreach ($couleursRenolit as $couleur) {
                $io->writeln(sprintf('- %s (ID: %d, Image: %s)', 
                    $couleur->getNom(), 
                    $couleur->getId(), 
                    $couleur->getUrlImage() ?: 'Aucune'
                ));
            }
            
            // Créer un client de test s'il n'existe pas
            $client = $this->entityManager->getRepository(Client::class)->findOneBy(['nom' => 'Client Test']);
            if (!$client) {
                $client = new Client();
                $client->setNom('Client Test')
                    ->setEmail('test@example.com')
                    ->setTel('0123456789');
                
                $this->entityManager->persist($client);
                $io->writeln('Client de test créé');
            }
            
            // Créer un projet de test s'il n'existe pas
            $projet = $this->entityManager->getRepository(Projet::class)->findOneBy(['refClient' => 'TEST-001']);
            if (!$projet) {
                $projet = new Projet();
                $projet->setRefClient('TEST-001')
                    ->setClient($client)
                    ->setLieu('123 Rue des Travaux Test')
                    ->setDescription('Projet de test pour configuration couleurs');
                
                $this->entityManager->persist($projet);
                $io->writeln('Projet de test créé');
            }
            
            // Créer une ConfPf de test s'il n'existe pas
            $confPf = $this->entityManager->getRepository(ConfPf::class)->findOneBy(['projet' => $projet]);
            if (!$confPf) {
                $confPf = new ConfPf();
                $confPf->setProjet($projet);
                
                $this->entityManager->persist($confPf);
                $io->writeln('Configuration porte-fenêtre de test créée');
            }
            
            $this->entityManager->flush();
            
            $io->success('Données de test prêtes !');
            $io->writeln(sprintf('URL de test: /configuration/pf/%d/couleurs/%d', 
                $projet->getId(), 
                $confPf->getId()
            ));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}