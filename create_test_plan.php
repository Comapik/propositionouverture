<?php
// Script pour créer une image de test simple
$planPath = __DIR__ . '/public/assets/plans/plan_fenetre.jpg';

// Créer une image de test simple (500x700 pixels)
$image = imagecreate(500, 700);

// Couleurs
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue = imagecolorallocate($image, 0, 100, 200);
$gray = imagecolorallocate($image, 200, 200, 200);

// Remplir le fond en blanc
imagefill($image, 0, 0, $white);

// Dessiner un cadre
imagerectangle($image, 10, 10, 490, 690, $black);

// Titre
imagestring($image, 5, 150, 30, 'PLAN TECHNIQUE', $black);
imagestring($image, 3, 180, 60, 'Fenetre/Porte', $blue);

// Dessiner un rectangle représentant la fenêtre
imagerectangle($image, 100, 150, 400, 450, $black);
imagerectangle($image, 105, 155, 395, 445, $black);

// Zone pour la valeur personnalisée (approximativement au centre)
imagestring($image, 4, 200, 300, 'VALEUR ICI:', $blue);
imagestring($image, 2, 210, 320, '(coordonnees: 250, 330)', $gray);

// Annotations
imagestring($image, 2, 20, 500, 'Largeur:', $black);
imagestring($image, 2, 20, 520, 'Hauteur:', $black);
imagestring($image, 2, 20, 540, 'Materiau:', $black);

// Instructions
imagestring($image, 2, 20, 600, 'Cette image sera remplacee par', $gray);
imagestring($image, 2, 20, 620, 'le vrai plan technique fourni.', $gray);
imagestring($image, 2, 20, 640, 'Copier plan_fenetre.jpg dans ce dossier.', $gray);

// Sauvegarder
imagejpeg($image, $planPath, 90);
imagedestroy($image);

echo "✅ Image de test créée : " . $planPath . "\n";
echo "📏 Cette image temporaire simule le plan technique.\n";
echo "🔄 Remplacez-la par l'image réelle fournie pour la production.\n";