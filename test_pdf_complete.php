<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Test génération PDF complète\n";

try {
    echo "1️⃣ Test instanciation service...\n";
    $service = new \App\Service\PdfGeneratorService(__DIR__);
    echo "✅ Service créé\n";
    
    echo "\n2️⃣ Test création entité mock...\n";
    // Créer un mock simple de ConfPf pour le test
    $mockConfPf = new class {
        public function getId() { return 1; }
        public function getProduit() { 
            return new class {
                public function getNom() { return 'Fenêtre Test'; }
            };
        }
        public function getCategorie() { 
            return new class {
                public function getNom() { return 'Catégorie Test'; }
            };
        }
        public function getProjet() {
            return new class {
                public function getRefClient() { return 'REF001'; }
            };
        }
    };
    echo "✅ Mock créé\n";
    
    echo "\n3️⃣ Test génération PDF...\n";
    $pdfPath = $service->generatePlanPdf($mockConfPf, 123.45);
    echo "✅ PDF généré : $pdfPath\n";
    
    echo "\n4️⃣ Vérification fichier...\n";
    $fullPath = __DIR__ . '/public' . $pdfPath;
    if (file_exists($fullPath)) {
        echo "✅ Fichier existe : " . round(filesize($fullPath)/1024, 2) . " KB\n";
    } else {
        echo "❌ Fichier non trouvé : $fullPath\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
}