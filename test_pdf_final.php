<?php
echo "🧪 Test final génération PDF avec image\n";

// Test rapide via curl
$url = "http://localhost:8888/configuration/pf/1/pdf/12";
$postData = "custom_value=199.9";

echo "🔄 Génération PDF via $url...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Ne pas suivre les redirections
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if ($httpCode == 302) {
    echo "✅ Redirection détectée (PDF généré)\n";
    
    // Extraire l'URL de redirection
    if (preg_match('/path=([^"&]+)/', $response, $matches)) {
        $encodedPath = $matches[1];
        $decodedPath = base64_decode(urldecode($encodedPath));
        echo "📄 Chemin PDF : $decodedPath\n";
        
        $fullPath = __DIR__ . '/public' . $decodedPath;
        if (file_exists($fullPath)) {
            echo "✅ Fichier existe : " . round(filesize($fullPath)/1024, 2) . " KB\n";
        }
    }
} else {
    echo "❌ Code HTTP inattendu : $httpCode\n";
    echo "📋 Début de réponse : " . substr($response, 0, 200) . "\n";
}

curl_close($ch);