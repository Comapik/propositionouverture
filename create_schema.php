<?php
// Script pour créer une version PNG du schéma technique fourni
// Basé sur l'image technique avec les dimensions 122, 141, 70, etc.

echo "📋 Création du schéma technique PNG...\n";

$schemaPath = __DIR__ . '/public/assets/plans/schemaProfil.png';

// Créer une image de 800x600 basée sur le schéma technique
$width = 800;
$height = 600;
$image = imagecreatetruecolor($width, $height);

// Couleurs
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$gray = imagecolorallocate($image, 200, 200, 200);
$lightgray = imagecolorallocate($image, 240, 240, 240);

// Remplir le fond
imagefill($image, 0, 0, $white);

// Dessiner le profil de fenêtre basé sur l'image fournie
// Coordonnées adaptées à une image 800x600

// Cadre extérieur (représentant la dimension 141)
$outerLeft = 100;
$outerRight = 500; // 141 unités * facteur d'échelle
$outerTop = 100;
$outerBottom = 450;

imagerectangle($image, $outerLeft, $outerTop, $outerRight, $outerBottom, $black);

// Profil de fenêtre complexe (simplifié du schéma)
// Partie gauche
imagerectangle($image, $outerLeft, $outerTop, $outerLeft + 40, $outerBottom, $gray);
// Partie droite  
imagerectangle($image, $outerRight - 40, $outerTop, $outerRight, $outerBottom, $gray);
// Partie centrale avec vitrage
imagerectangle($image, $outerLeft + 60, $outerTop + 60, $outerRight - 60, $outerBottom - 60, $lightgray);

// Ajouter les cotes principales
$font = 3;

// Cote 141 (largeur totale) - en bas
imagestring($image, $font, $outerLeft + 150, $outerBottom + 20, "141", $black);
// Flèches pour la cote 141
imageline($image, $outerLeft, $outerBottom + 15, $outerRight, $outerBottom + 15, $black);
imageline($image, $outerLeft, $outerBottom + 10, $outerLeft, $outerBottom + 20, $black);
imageline($image, $outerRight, $outerBottom + 10, $outerRight, $outerBottom + 20, $black);

// Cote 122 (largeur utile) - position où l'utilisateur saisira sa valeur
imagestring($image, $font, $outerLeft + 40 + 50, $outerBottom + 40, "122", $black);
// Ligne de cote pour 122
$cote122Left = $outerLeft + 40;
$cote122Right = $outerRight - 40;
imageline($image, $cote122Left, $outerBottom + 35, $cote122Right, $outerBottom + 35, $black);
imageline($image, $cote122Left, $outerBottom + 30, $cote122Left, $outerBottom + 40, $black);
imageline($image, $cote122Right, $outerBottom + 30, $cote122Right, $outerBottom + 40, $black);

// Cote 70 (hauteur) - à droite
imagestring($image, $font, $outerRight + 10, $outerTop + 150, "70", $black);
// Ligne de cote pour 70
imageline($image, $outerRight + 5, $outerTop, $outerRight + 5, $outerBottom, $black);
imageline($image, $outerRight, $outerTop, $outerRight + 10, $outerTop, $black);
imageline($image, $outerRight, $outerBottom, $outerRight + 10, $outerBottom, $black);

// Marquer l'emplacement où la valeur utilisateur sera ajoutée
imagestring($image, 2, $cote122Left + 80, $outerBottom + 55, ">>> VALEUR UTILISATEUR ICI <<<", $black);

// Titre
imagestring($image, 5, 250, 50, "Schema Profil Fenetre", $black);

// Sauvegarder
imagepng($image, $schemaPath);
imagedestroy($image);

echo "✅ Schéma créé : $schemaPath\n";
echo "📏 Dimensions : 800x600 pixels\n";
echo "🎯 Emplacement valeur utilisateur : près de la cote 122\n";