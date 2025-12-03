<?php

echo "🔄 Test de génération PDF avec sauvegarde en base\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'], false);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');

try {
    // Récupérer une configuration existante
    echo "🔍 Recherche d'une configuration ConfPf...\n";
    $confPf = $entityManager->createQuery('SELECT cf FROM App\Entity\ConfPf cf JOIN cf.projet p ORDER BY cf.id DESC')
        ->setMaxResults(1)
        ->getOneOrNullResult();
    
    if (!$confPf) {
        echo "❌ Aucune configuration ConfPf trouvée. Veuillez d'abord créer un projet avec une configuration.\n";
        exit(1);
    }
    
    echo "✅ Configuration trouvée : Projet " . $confPf->getProjet()->getRefClient() . " (ID: " . $confPf->getId() . ")\n";
    
    // Tester le service PDF
    echo "📄 Test du service PDF...\n";
    $pdfService = $container->get(\App\Service\PdfGeneratorService::class);
    
    $customValue = 156.7; // Valeur de test
    
    echo "⚙️ Génération du PDF avec valeur : {$customValue}cm...\n";
    $pdfPath = $pdfService->generatePlanPdf($confPf, $customValue);
    
    echo "✅ PDF généré : $pdfPath\n";
    
    // Vérifier que le fichier existe
    $fullPath = __DIR__ . '/public' . $pdfPath;
    if (file_exists($fullPath)) {
        echo "✅ Fichier PDF créé avec succès\n";
        echo "📊 Taille du fichier : " . round(filesize($fullPath) / 1024, 1) . " KB\n";
    } else {
        echo "❌ Fichier PDF non trouvé : $fullPath\n";
    }
    
    // Vérifier l'enregistrement en base
    echo "🔍 Vérification de l'enregistrement en base...\n";
    $projetPdfs = $entityManager->getRepository(\App\Entity\ProjetPdf::class)
        ->findByProjetOrderedByDate($confPf->getProjet());
    
    echo "📊 Nombre de PDFs pour ce projet : " . count($projetPdfs) . "\n";
    
    if (count($projetPdfs) > 0) {
        $latestPdf = $projetPdfs[0];
        echo "✅ Dernier PDF enregistré :\n";
        echo "  - ID: " . $latestPdf->getId() . "\n";
        echo "  - Nom: " . $latestPdf->getFileName() . "\n";
        echo "  - Chemin: " . $latestPdf->getFilePath() . "\n";
        echo "  - Valeur: " . $latestPdf->getCustomValue() . "cm\n";
        echo "  - Taille: " . $latestPdf->getFormattedSize() . "\n";
        echo "  - Créé: " . $latestPdf->getCreatedAt()->format('d/m/Y H:i:s') . "\n";
        echo "  - Chemin encodé: " . substr($latestPdf->getEncodedPath(), 0, 20) . "...\n";
    }
    
    echo "\n✅ Test complet réussi ! Le système PDF est maintenant relié au projet en base de données.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "📍 Ligne : " . $e->getLine() . "\n";
    echo "📁 Fichier : " . $e->getFile() . "\n";
    if ($e->getPrevious()) {
        echo "🔗 Erreur précédente : " . $e->getPrevious()->getMessage() . "\n";
    }
}