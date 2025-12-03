<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔄 Test PDF avec rotation 90° anti-horaire\n";

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Test Rotation');
    $pdf->SetTitle('Test PDF avec valeur rotée');
    $pdf->AddPage();
    
    // Ajouter l'image (si elle existe)
    $imagePath = __DIR__ . '/public/assets/plans/schemaProfil.png';
    if (file_exists($imagePath)) {
        $pdf->Image($imagePath, 15, 20, 180, 135, 'PNG');
        echo "✅ Image ajoutée\n";
    }
    
    // Ajouter la valeur avec rotation 90° anti-horaire
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    
    $x = 20; // 20mm du bord gauche
    $y = 100; // 100mm du haut
    
    // Rotation 90° anti-horaire
    $pdf->StartTransform();
    $pdf->Rotate(90, $x, $y);
    
    $pdf->SetXY($x, $y);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Cell(35, 8, '145.7 mm', 1, 0, 'C', true);
    
    $pdf->StopTransform();
    
    // Sauvegarder
    $outputPath = __DIR__ . '/public/uploads/pdf/test_rotation_' . date('YmdHis') . '.pdf';
    $outputDir = dirname($outputPath);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $pdf->Output($outputPath, 'F');
    
    if (file_exists($outputPath)) {
        echo "✅ PDF avec rotation généré !\n";
        echo "📁 Chemin : $outputPath\n";
        echo "📊 Taille : " . round(filesize($outputPath)/1024, 2) . " KB\n";
        $relativePath = str_replace(__DIR__ . '/public', '', $outputPath);
        echo "🌐 URL : http://localhost:8888$relativePath\n";
    } else {
        echo "❌ Échec de la génération\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}