<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🧪 Test rapide du service PdfGeneratorService\n";

// Test 1: Vérification des dépendances
echo "\n1️⃣ Test des dépendances TCPDF:\n";
if (class_exists('TCPDF')) {
    echo "✅ TCPDF disponible\n";
} else {
    echo "❌ TCPDF non trouvé\n";
}

// Test 2: Vérification de l'image
echo "\n2️⃣ Test de l'image plan:\n";
$planPath = __DIR__ . '/public/assets/plans/plan_fenetre.jpg';
if (file_exists($planPath)) {
    echo "✅ Image plan_fenetre.jpg existe (" . round(filesize($planPath)/1024, 2) . " KB)\n";
} else {
    echo "❌ Image plan_fenetre.jpg manquante\n";
}

// Test 3: Répertoire PDF
echo "\n3️⃣ Test du répertoire PDF:\n";
$pdfDir = __DIR__ . '/public/pdf';
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0777, true);
    echo "✅ Répertoire PDF créé\n";
} else {
    echo "✅ Répertoire PDF existe\n";
}

// Test 4: Services Symfony
echo "\n4️⃣ Test service Symfony:\n";
try {
    $projectDir = __DIR__;
    $service = new \App\Service\PdfGeneratorService($projectDir);
    echo "✅ Service PdfGeneratorService instanciable\n";
    
    // Test méthode
    if (method_exists($service, 'generatePlanPdf')) {
        echo "✅ Méthode generatePlanPdf existe\n";
    } else {
        echo "❌ Méthode generatePlanPdf manquante\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur service: " . $e->getMessage() . "\n";
}

echo "\n🎯 Diagnostic terminé!\n";