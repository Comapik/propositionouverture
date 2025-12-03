<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔧 Test minimal service PDF\n";

// Vérifier l'image
$imagePath = __DIR__ . '/public/assets/plans/schemaProfil.png';
if (!file_exists($imagePath)) {
    echo "❌ Image manquante : $imagePath\n";
    exit(1);
}

echo "✅ Image trouvée : " . basename($imagePath) . "\n";

// Test création PDF directe avec TCPDF
try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Test');
    $pdf->SetTitle('Test PDF avec image');
    $pdf->AddPage();
    
    echo "📄 PDF créé, ajout de l'image...\n";
    
    // Ajouter l'image directement
    $pdf->Image($imagePath, 15, 20, 180, 135, 'PNG');
    
    echo "🖼️ Image ajoutée, ajout du texte...\n";
    
    // Ajouter du texte
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetXY(90, 170);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(35, 8, '123.5 mm', 1, 0, 'C', true);
    
    // Sauvegarder
    $outputPath = __DIR__ . '/public/uploads/pdf/test_direct_' . date('YmdHis') . '.pdf';
    $outputDir = dirname($outputPath);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $pdf->Output($outputPath, 'F');
    
    if (file_exists($outputPath)) {
        echo "✅ PDF généré avec succès !\n";
        echo "📁 Chemin : $outputPath\n";
        echo "📊 Taille : " . round(filesize($outputPath)/1024, 2) . " KB\n";
    } else {
        echo "❌ Échec de la génération\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}