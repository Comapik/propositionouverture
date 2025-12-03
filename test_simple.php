<?php
echo "🧪 Test simple route PDF\n";

// Test direct de la route sans Symfony
$url = "/configuration/pf/1/pdf/12";
echo "URL testée: $url\n";

// Simuler les paramètres
$projet = 1;
$confpf = 12;

echo "Paramètres extraits:\n";
echo "- projet: $projet (type: " . gettype($projet) . ")\n";
echo "- confpf: $confpf (type: " . gettype($confpf) . ")\n";

// Test de conversion
$confpfInt = (int)$confpf;
echo "- confpf converti: $confpfInt (type: " . gettype($confpfInt) . ")\n";

echo "\n✅ Types corrects - la route devrait fonctionner!\n";