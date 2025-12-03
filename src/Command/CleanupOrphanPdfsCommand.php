<?php

namespace App\Command;

use App\Entity\ProjetPdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanupOrphanPdfsCommand extends Command
{
    protected static $defaultName = 'app:cleanup-orphan-pdfs';
    protected static $defaultDescription = 'Nettoie les fichiers PDF orphelins non reliés aux projets';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:cleanup-orphan-pdfs');
        $this->setDescription('Nettoie les fichiers PDF orphelins non reliés aux projets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🧹 NETTOYAGE DES PDFs ORPHELINS');

        // Récupérer tous les PDFs enregistrés en base
        $projetPdfs = $this->entityManager->getRepository(ProjetPdf::class)->findAll();
        $registeredFiles = [];

        $io->section('PDFs enregistrés en base de données:');
        foreach ($projetPdfs as $projetPdf) {
            $filename = $projetPdf->getFileName();
            $registeredFiles[] = $filename;
            $io->text("- {$filename} (Projet: {$projetPdf->getProjet()->getId()})");
        }

        $io->text("Total PDFs en base: " . count($registeredFiles));

        // Lister tous les fichiers PDF sur le disque
        $pdfDirectory = $this->projectDir . '/public/uploads/pdf/';
        $filesOnDisk = [];

        if (is_dir($pdfDirectory)) {
            $files = scandir($pdfDirectory);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                    $filesOnDisk[] = $file;
                }
            }
        }

        $io->section('PDFs sur le disque:');
        foreach ($filesOnDisk as $file) {
            $io->text("- {$file}");
        }

        $io->text("Total PDFs sur disque: " . count($filesOnDisk));

        // Trouver les fichiers orphelins
        $orphanFiles = array_diff($filesOnDisk, $registeredFiles);

        if (empty($orphanFiles)) {
            $io->success('Aucun fichier orphelin trouvé. Tous les PDFs sont correctement référencés.');
            return Command::SUCCESS;
        }

        $io->section('🗑️  Fichiers orphelins détectés:');
        $totalSize = 0;

        foreach ($orphanFiles as $orphanFile) {
            $fullPath = $pdfDirectory . $orphanFile;
            $size = filesize($fullPath);
            $totalSize += $size;
            $sizeFormatted = round($size / 1024, 2) . ' KB';
            $io->text("- {$orphanFile} ({$sizeFormatted})");
        }

        $io->text("Nombre de fichiers orphelins: " . count($orphanFiles));
        $io->text("Taille totale: " . round($totalSize / 1024, 2) . " KB");

        if (!$io->confirm('Voulez-vous supprimer ces fichiers orphelins ?', false)) {
            $io->info('Annulation du nettoyage.');
            return Command::SUCCESS;
        }

        $io->section('🚨 SUPPRESSION DES FICHIERS ORPHELINS:');

        $deletedCount = 0;
        $deletedSize = 0;

        foreach ($orphanFiles as $orphanFile) {
            $fullPath = $pdfDirectory . $orphanFile;
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                if (unlink($fullPath)) {
                    $io->text("✅ Supprimé: {$orphanFile}");
                    $deletedCount++;
                    $deletedSize += $size;
                } else {
                    $io->error("Erreur lors de la suppression: {$orphanFile}");
                }
            }
        }

        $io->success([
            'RÉSUMÉ DU NETTOYAGE:',
            "- Fichiers supprimés: {$deletedCount}",
            "- Espace libéré: " . round($deletedSize / 1024, 2) . " KB",
            "- PDFs conservés: " . count($registeredFiles) . " (liés aux projets)"
        ]);

        return Command::SUCCESS;
    }
}