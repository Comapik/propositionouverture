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

// Simuler une requête POST avec une valeur personnalisée
$request = Request::create('/configuration/pf/1/pdf/12', 'POST', [
    'custom_value' => '123.45'
]);

try {
    $response = $kernel->handle($request);
    
    echo "✅ Statut HTTP: " . $response->getStatusCode() . "\n";
    echo "📍 Location: " . $response->headers->get('Location', 'none') . "\n";
    
    if ($response->getStatusCode() === 302) {
        echo "✅ Redirection détectée - PDF généré avec succès!\n";
        echo "📄 URL de téléchargement: " . $response->headers->get('Location') . "\n";
    } else {
        echo "❌ Code de statut inattendu: " . $response->getStatusCode() . "\n";
        
        // Afficher le début de la réponse pour debug
        $content = $response->getContent();
        if (strpos($content, '<!DOCTYPE html>') === 0) {
            echo "📝 Réponse HTML (début): " . substr($content, 0, 300) . "...\n";
        } else {
            echo "📝 Contenu: " . substr($content, 0, 500) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "📋 Classe: " . get_class($e) . "\n";
    if ($e->getPrevious()) {
        echo "🔍 Cause: " . $e->getPrevious()->getMessage() . "\n";
    }
}

$kernel->terminate($request, $response ?? null);