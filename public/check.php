<?php
/**
 * Script de diagnostic pour o2switch
 * Accédez à ce fichier via https://votre-domaine.com/check.php
 */

echo "<h1>Diagnostic Symfony - o2switch</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Version PHP
echo "<h2>1. Version PHP</h2>";
$phpVersion = phpversion();
echo "PHP Version: <strong>$phpVersion</strong> ";
echo (version_compare($phpVersion, '8.2.0') >= 0) 
    ? '<span class="ok">✓ OK (>= 8.2 requis)</span>' 
    : '<span class="error">✗ ERREUR (>= 8.2 requis)</span>';
echo "<br>";

// 2. Extensions PHP requises
echo "<h2>2. Extensions PHP</h2>";
$requiredExtensions = ['ctype', 'iconv', 'json', 'mbstring', 'pdo', 'pdo_mysql'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "$ext: ";
    echo $loaded ? '<span class="ok">✓ Chargée</span>' : '<span class="error">✗ Manquante</span>';
    echo "<br>";
}

// 3. Fichiers/dossiers requis
echo "<h2>3. Fichiers et Dossiers</h2>";
$projectRoot = dirname(__DIR__);
$requiredPaths = [
    '/vendor/autoload.php' => 'Autoloader Composer',
    '/var' => 'Dossier var (cache/log)',
    '/config/packages' => 'Configuration',
    '/.env' => 'Fichier environnement (.env)',
];

foreach ($requiredPaths as $path => $description) {
    $fullPath = $projectRoot . $path;
    $exists = file_exists($fullPath);
    echo "$description ($path): ";
    echo $exists ? '<span class="ok">✓ Existe</span>' : '<span class="error">✗ Manquant</span>';
    
    if ($exists && is_dir($fullPath)) {
        $writable = is_writable($fullPath);
        echo $writable ? ' <span class="ok">(✓ Écriture)</span>' : ' <span class="error">(✗ Pas d\'écriture)</span>';
    }
    echo "<br>";
}

// 4. Variables d'environnement
echo "<h2>4. Variables d'Environnement</h2>";
if (file_exists($projectRoot . '/.env')) {
    $envContent = file_get_contents($projectRoot . '/.env');
    echo ".env trouvé<br>";
    
    // Chercher APP_ENV
    if (preg_match('/APP_ENV=(\w+)/', $envContent, $matches)) {
        echo "APP_ENV: <strong>{$matches[1]}</strong> ";
        echo ($matches[1] === 'prod') 
            ? '<span class="ok">✓ Production</span>' 
            : '<span class="warning">⚠ Développement (devrait être prod sur o2switch)</span>';
        echo "<br>";
    }
    
    // Chercher DATABASE_URL
    if (preg_match('/DATABASE_URL=["\']?([^"\'\n]+)/', $envContent, $matches)) {
        $dbUrl = $matches[1];
        $dbUrlMasked = preg_replace('/(:\/\/)([^:]+):([^@]+)(@)/', '$1****:****$4', $dbUrl);
        echo "DATABASE_URL: <strong>$dbUrlMasked</strong> ";
        echo (strpos($dbUrl, 'localhost') !== false || strpos($dbUrl, '127.0.0.1') !== false) 
            ? '<span class="warning">⚠ Configuration locale (besoin .env.prod.local ?)</span>' 
            : '<span class="ok">✓ Configuré</span>';
        echo "<br>";
    }
} else {
    echo '<span class="error">✗ Fichier .env manquant</span><br>';
}

// Vérifier .env.local
$envLocal = $projectRoot . '/.env.local';
echo "<br>.env.local: ";
echo file_exists($envLocal) ? '<span class="ok">✓ Existe</span>' : '<span class="warning">⚠ N\'existe pas</span>';
echo "<br>";

// Vérifier .env.prod.local
$envProdLocal = $projectRoot . '/.env.prod.local';
echo ".env.prod.local: ";
echo file_exists($envProdLocal) ? '<span class="ok">✓ Existe</span>' : '<span class="warning">⚠ N\'existe pas</span>';
echo "<br>";

// 5. Permissions critiques
echo "<h2>5. Permissions</h2>";
$varDir = $projectRoot . '/var';
$cacheDir = $projectRoot . '/var/cache';
$logDir = $projectRoot . '/var/log';

foreach ([$varDir, $cacheDir, $logDir] as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir);
        echo basename($dir) . "/ : Permissions $perms ";
        echo $writable ? '<span class="ok">✓ Écriture OK</span>' : '<span class="error">✗ Pas d\'écriture</span>';
        echo "<br>";
    }
}

// 6. Test de connexion base de données
echo "<h2>6. Connexion Base de Données</h2>";
try {
    if (file_exists($projectRoot . '/vendor/autoload.php')) {
        require_once $projectRoot . '/vendor/autoload.php';
        
        // Charger les variables d'environnement
        if (class_exists('Symfony\Component\Dotenv\Dotenv')) {
            $dotenv = new Symfony\Component\Dotenv\Dotenv();
            $dotenv->loadEnv($projectRoot . '/.env');
        }
        
        $dbUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
        
        if ($dbUrl) {
            // Parser l'URL
            preg_match('/^(\w+):\/\/([^:]+):([^@]+)@([^:\/]+)(?::(\d+))?\/(.+?)(?:\?.*)?$/', $dbUrl, $matches);
            
            if ($matches) {
                $driver = $matches[1];
                $user = $matches[2];
                $pass = $matches[3];
                $host = $matches[4];
                $port = $matches[5] ?? 3306;
                $dbname = preg_replace('/\?.*$/', '', $matches[6]);
                
                try {
                    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    echo '<span class="ok">✓ Connexion réussie à la base de données</span><br>';
                    
                    // Vérifier les tables
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    echo "Nombre de tables: <strong>" . count($tables) . "</strong><br>";
                    
                    if (count($tables) === 0) {
                        echo '<span class="warning">⚠ ATTENTION: La base est vide. Exécutez les migrations!</span><br>';
                    }
                    
                } catch (PDOException $e) {
                    echo '<span class="error">✗ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                }
            }
        } else {
            echo '<span class="error">✗ DATABASE_URL non définie</span><br>';
        }
    }
} catch (Exception $e) {
    echo '<span class="error">✗ Erreur: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
}

// 7. Recommandations
echo "<h2>7. Actions Recommandées</h2>";
echo "<ol>";
echo "<li>Assurez-vous que <strong>APP_ENV=prod</strong> dans .env.local ou .env.prod.local</li>";
echo "<li>Configurez DATABASE_URL avec vos identifiants o2switch</li>";
echo "<li>Exécutez: <code>composer install --no-dev --optimize-autoloader</code></li>";
echo "<li>Exécutez: <code>php bin/console doctrine:migrations:migrate --no-interaction</code></li>";
echo "<li>Exécutez: <code>php bin/console cache:clear --env=prod</code></li>";
echo "<li>Définissez les permissions: <code>chmod -R 775 var/</code></li>";
echo "<li><strong>SUPPRIMEZ ce fichier check.php après diagnostic</strong></li>";
echo "</ol>";

echo "<hr><p><em>Généré le " . date('Y-m-d H:i:s') . "</em></p>";
