<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv(__DIR__.'/.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer les services nécessaires
$entityManager = $container->get('doctrine.orm.entity_manager');
$pdfGenerator = $container->get('App\Service\PdfGeneratorService');

// Trouver une configuration de test
$confRepo = $entityManager->getRepository(\App\Entity\ConfPf::class);
$confPf = $confRepo->findOneBy([]);

if (!$confPf) {
    echo "❌ Aucune configuration ConfPf trouvée. Exécutez d'abord: php bin/console test:configuration\n";
    exit(1);
}

echo "✅ Configuration trouvée: ID " . $confPf->getId() . "\n";
echo "📁 Projet: " . $confPf->getProjet()->getRefClient() . "\n";

try {
    echo "🔄 Génération du PDF...\n";
    $pdfPath = $pdfGenerator->generatePlanPdf($confPf, 123.45);
    echo "✅ PDF généré avec succès!\n";
    echo "📄 Chemin: " . $pdfPath . "\n";
    
    $fullPath = __DIR__ . '/public' . $pdfPath;
    if (file_exists($fullPath)) {
        echo "📏 Taille: " . round(filesize($fullPath) / 1024, 2) . " KB\n";
        echo "🌐 URL de test: http://localhost" . $pdfPath . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur lors de la génération: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
}