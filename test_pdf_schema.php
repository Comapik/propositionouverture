<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🧪 Test génération PDF avec nouveau schéma\n";

try {
    // Créer le service
    $service = new \App\Service\PdfGeneratorService(__DIR__);
    
    // Créer un mock de ConfPf
    $mockConfPf = new class {
        public function getId() { return 1; }
        public function getProduit() { 
            return new class {
                public function getNom() { return 'Fenêtre PVC 70mm'; }
            };
        }
        public function getCategorie() { 
            return new class {
                public function getNom() { return 'Fenêtre standard'; }
            };
        }
        public function getProjet() {
            return new class {
                public function getRefClient() { return 'PROJ-2025-001'; }
            };
        }
    };
    
    echo "🔄 Génération PDF avec valeur 125.5...\n";
    $pdfPath = $service->generatePlanPdf($mockConfPf, 125.5);
    
    $fullPath = __DIR__ . '/public' . $pdfPath;
    if (file_exists($fullPath)) {
        echo "✅ PDF généré avec succès !\n";
        echo "📄 Chemin : $pdfPath\n";
        echo "📊 Taille : " . round(filesize($fullPath)/1024, 2) . " KB\n";
        echo "🌐 URL test : http://localhost:8888$pdfPath\n";
    } else {
        echo "❌ Fichier PDF non trouvé : $fullPath\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "📋 Trace : " . $e->getFile() . ":" . $e->getLine() . "\n";
}