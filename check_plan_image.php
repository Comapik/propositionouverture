<?php
// Script de test pour vérifier que l'image du plan existe
$planPath = __DIR__ . '/../public/assets/plans/plan_fenetre.jpg';

if (!file_exists($planPath)) {
    echo "⚠️  ATTENTION: L'image plan_fenetre.jpg n'existe pas encore.\n";
    echo "📁 Emplacement attendu: " . $planPath . "\n";
    echo "📝 Vous devez copier l'image fournie à cet emplacement pour que la génération PDF fonctionne.\n\n";
    echo "💡 Commande pour copier (exemple):\n";
    echo "   cp /path/to/your/plan_fenetre.jpg " . dirname($planPath) . "/\n";
} else {
    echo "✅ L'image plan_fenetre.jpg existe!\n";
    echo "📁 Chemin: " . $planPath . "\n";
    echo "📏 Taille: " . round(filesize($planPath) / 1024, 2) . " KB\n";
}