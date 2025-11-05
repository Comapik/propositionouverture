<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

// Boot Symfony kernel
$kernel = new Kernel($_ENV['APP_ENV'], (bool) ($_ENV['APP_DEBUG'] ?? false));
$kernel->boot();

// Get entity manager
$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

// Insert sample suppliers
$fournisseurs = [
    // PVC (produit_id = 1)
    ['produit_id' => 1, 'marque' => 'Schüco'],
    ['produit_id' => 1, 'marque' => 'VEKA'],
    ['produit_id' => 1, 'marque' => 'Rehau'],
    ['produit_id' => 1, 'marque' => 'Profialis'],
    
    // ALU (produit_id = 2)  
    ['produit_id' => 2, 'marque' => 'Technal'],
    ['produit_id' => 2, 'marque' => 'K-Line'],
    ['produit_id' => 2, 'marque' => 'Reynaers'],
    ['produit_id' => 2, 'marque' => 'Sapa'],
    
    // Bois (produit_id = 3)
    ['produit_id' => 3, 'marque' => 'Kömmerling'],
    ['produit_id' => 3, 'marque' => 'Internorm'],
    ['produit_id' => 3, 'marque' => 'Bois Noble'],
];

foreach ($fournisseurs as $data) {
    $sql = "INSERT INTO fournisseurs (produit_id, marque) VALUES (?, ?) ON DUPLICATE KEY UPDATE marque = VALUES(marque)";
    $stmt = $entityManager->getConnection()->prepare($sql);
    $stmt->execute([$data['produit_id'], $data['marque']]);
}

echo "Fournisseurs d'exemple insérés avec succès!\n";