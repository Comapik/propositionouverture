<?php
// Test d'accès aux images
header('Content-Type: text/html; charset=utf-8');

$projectDir = dirname(__DIR__);
$uploadsDir = $projectDir . '/public/uploads/projets/1';

echo "<h2>🔍 Diagnostic des images</h2>";

echo "<h3>Répertoire: $uploadsDir</h3>";
if (is_dir($uploadsDir)) {
    echo "✅ Répertoire existe<br>";
    
    $files = glob($uploadsDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    echo "<strong>Fichiers trouvés: " . count($files) . "</strong><br>";
    
    foreach ($files as $file) {
        $filename = basename($file);
        $webPath = '/uploads/projets/1/' . $filename;
        $size = filesize($file);
        $readable = is_readable($file);
        
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>$filename</strong><br>";
        echo "Taille: " . number_format($size) . " bytes<br>";
        echo "Lisible: " . ($readable ? '✅' : '❌') . "<br>";
        echo "Chemin web: $webPath<br>";
        echo "<img src='$webPath' style='max-width: 200px; height: auto; border: 1px solid red;' alt='Test'>";
        echo "</div>";
    }
} else {
    echo "❌ Répertoire n'existe pas<br>";
}

// Test du serveur
echo "<h3>🖥️ Informations serveur</h3>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
?>