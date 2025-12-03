<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv(__DIR__.'/.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();

// Simuler une requête vers notre route PDF
$request = Request::create('/configuration/pf/1/pdf/12', 'GET');

try {
    $response = $kernel->handle($request);
    
    echo "✅ Statut HTTP: " . $response->getStatusCode() . "\n";
    echo "📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ La page PDF se charge correctement!\n";
        echo "📝 Contenu (extrait): " . substr($response->getContent(), 0, 200) . "...\n";
    } else {
        echo "❌ Erreur HTTP: " . $response->getStatusCode() . "\n";
        echo "📋 Contenu: " . $response->getContent() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "📋 Classe: " . get_class($e) . "\n";
    if ($e->getPrevious()) {
        echo "🔍 Cause: " . $e->getPrevious()->getMessage() . "\n";
    }
}

$kernel->terminate($request, $response ?? null);