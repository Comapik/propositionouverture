<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🎯 Test position valeur utilisateur : 20mm gauche, 100mm haut\n";

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Test Position');
    $pdf->SetTitle('Test Position Valeur');
    $pdf->AddPage();
    
    // Ajouter l'image du schéma
    $imagePath = __DIR__ . '/public/assets/plans/schemaProfil.png';
    if (file_exists($imagePath)) {
        $pdf->Image($imagePath, 15, 20, 180, 135, 'PNG');
        echo "✅ Image ajoutée\n";
    }
    
    // Ajouter la valeur à la position exacte : 20mm gauche, 100mm haut
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(255, 0, 0); // Rouge
    $pdf->SetXY(20, 100); // Position exacte
    $pdf->SetFillColor(255, 255, 255); // Fond blanc
    $pdf->SetDrawColor(0, 0, 0); // Bordure noire
    $pdf->Cell(35, 8, '125.5 mm', 1, 0, 'C', true);
    
    echo "✅ Valeur positionnée à x=20mm, y=100mm\n";
    
    // Sauvegarder
    $outputPath = __DIR__ . '/public/uploads/pdf/test_position_' . date('YmdHis') . '.pdf';
    $pdf->Output($outputPath, 'F');
    
    if (file_exists($outputPath)) {
        echo "✅ PDF généré : " . basename($outputPath) . "\n";
        echo "📊 Taille : " . round(filesize($outputPath)/1024, 2) . " KB\n";
        echo "🌐 Test : http://localhost:8888/uploads/pdf/" . basename($outputPath) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}