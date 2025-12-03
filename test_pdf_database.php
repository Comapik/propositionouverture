<?php

echo "🔄 Test du système PDF avec base de données\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');

try {
    // Vérifier que la table existe
    echo "✅ Vérification de la base de données...\n";
    
    $connection = $entityManager->getConnection();
    $tables = $connection->createSchemaManager()->listTables();
    $tableNames = array_map(fn($table) => $table->getName(), $tables);
    
    if (in_array('projet_pdf', $tableNames)) {
        echo "✅ Table projet_pdf existe\n";
        
        // Compter les enregistrements
        $count = $entityManager->createQuery('SELECT COUNT(pp) FROM App\Entity\ProjetPdf pp')
            ->getSingleScalarResult();
        echo "📊 Nombre de PDFs enregistrés : $count\n";
        
        // Afficher les derniers PDFs
        if ($count > 0) {
            $latestPdfs = $entityManager->createQuery(
                'SELECT pp FROM App\Entity\ProjetPdf pp ORDER BY pp.createdAt DESC'
            )->setMaxResults(3)->getResult();
            
            echo "\n📄 Derniers PDFs générés :\n";
            foreach ($latestPdfs as $pdf) {
                echo "  • " . $pdf->getFileName() . " (Projet: " . $pdf->getProjet()->getRefClient() . 
                     ", Valeur: " . $pdf->getCustomValue() . "cm, Taille: " . $pdf->getFormattedSize() . 
                     ", Créé: " . $pdf->getCreatedAt()->format('d/m/Y H:i') . ")\n";
            }
        }
    } else {
        echo "❌ Table projet_pdf n'existe pas\n";
    }
    
    // Tester la classe ProjetPdf
    echo "\n🧪 Test de création d'une instance ProjetPdf...\n";
    $projetPdf = new \App\Entity\ProjetPdf();
    $projetPdf->setFileName('test.pdf');
    $projetPdf->setFilePath('/uploads/pdf/test.pdf');
    $projetPdf->setCustomValue(123.45);
    $projetPdf->setFileSize(15000);
    
    echo "✅ Instance ProjetPdf créée avec succès\n";
    echo "  - Nom: " . $projetPdf->getFileName() . "\n";
    echo "  - Chemin: " . $projetPdf->getFilePath() . "\n";
    echo "  - Chemin encodé: " . $projetPdf->getEncodedPath() . "\n";
    echo "  - Valeur: " . $projetPdf->getCustomValue() . "cm\n";
    echo "  - Taille formatée: " . $projetPdf->getFormattedSize() . "\n";
    echo "  - Date création: " . $projetPdf->getCreatedAt()->format('d/m/Y H:i:s') . "\n";
    
    echo "\n✅ Système PDF avec base de données opérationnel !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "📍 Ligne : " . $e->getLine() . "\n";
    echo "📁 Fichier : " . $e->getFile() . "\n";
}