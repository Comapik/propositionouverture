<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Vérification du contenu PDF généré\n";

// Vérifier si le schéma existe
$schemaPath = __DIR__ . '/public/assets/plans/schemaProfil.png';
if (file_exists($schemaPath)) {
    $imageInfo = getimagesize($schemaPath);
    echo "✅ Schéma trouvé : " . basename($schemaPath) . "\n";
    echo "📏 Dimensions : {$imageInfo[0]}x{$imageInfo[1]} pixels\n";
    echo "🎨 Type : " . $imageInfo['mime'] . "\n";
} else {
    echo "❌ Schéma manquant : $schemaPath\n";
}

// Vérifier le dernier PDF généré
$pdfDir = __DIR__ . '/public/uploads/pdf/';
$pdfFiles = glob($pdfDir . 'plan_*.pdf');
if (empty($pdfFiles)) {
    echo "❌ Aucun PDF trouvé\n";
    exit;
}

// Trier par date de modification (le plus récent en premier)
usort($pdfFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestPdf = $pdfFiles[0];
echo "\n📄 PDF le plus récent : " . basename($latestPdf) . "\n";
echo "📊 Taille : " . round(filesize($latestPdf)/1024, 2) . " KB\n";
echo "🕒 Modifié : " . date('Y-m-d H:i:s', filemtime($latestPdf)) . "\n";

// URL pour télécharger via le navigateur
$relativePath = str_replace(__DIR__ . '/public', '', $latestPdf);
echo "🌐 URL : http://localhost:8888$relativePath\n";

echo "\n✅ Vérification terminée !\n";