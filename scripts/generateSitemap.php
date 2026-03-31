<?php
/**
 * generateSitemap.php
 * 
 * Script pour générer dynamiquement le sitemap.xml avec toutes les offres et entreprises
 * Exécution : php generateSitemap.php (depuis /srv/http/StageLink/public_html/)
 * 
 * Usage dans cron (optionnel) :
 * 0 2 * * * cd /srv/http/StageLink/public_html && php generateSitemap.php
 */

declare(strict_types=1);

// Charger les variables d'environnement
$env_file = __DIR__ . '/../../.env';
$envVars = [];

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
} else {
    die("❌ Fichier .env non trouvé à : $env_file\n");
}

// Connexion directe à Railway MySQL
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $envVars['DB_HOST'] ?? 'localhost',
        $envVars['DB_PORT'] ?? '3306',
        $envVars['DB_NAME'] ?? 'railway'
    );
    
    $pdo = new PDO(
        $dsn,
        $envVars['DB_USER'] ?? 'root',
        $envVars['DB_PASS'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✅ Connecté à la BDD Railway\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion BDD : " . $e->getMessage() . "\n");
}

// Récupérer les offres actives
$offres = $pdo->query("
    SELECT Id_offre, date_creation_offre 
    FROM OFFRE 
    WHERE statut = 'actif' 
    ORDER BY date_creation_offre DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les entreprises (pas de colonne date, utiliser date actuelle)
$entreprises = $pdo->query("
    SELECT Id_entreprise
    FROM ENTREPRISE 
    ORDER BY Id_entreprise DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Construire le XML
$xml = <<<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Pages statiques principales -->
    <url>
        <loc>https://stagelink.com/</loc>
        <lastmod>2025-03-31</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>https://stagelink.com/offres</loc>
        <lastmod>2025-03-31</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://stagelink.com/entreprises</loc>
        <lastmod>2025-03-31</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://stagelink.com/login</loc>
        <lastmod>2025-03-31</lastmod>
        <changefreq>never</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://stagelink.com/mentions-legales</loc>
        <lastmod>2025-03-31</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

EOF;

// Ajouter les offres
foreach ($offres as $offre) {
    $id = htmlspecialchars((string)$offre['Id_offre'], ENT_XML1);
    $lastmod = htmlspecialchars((string)$offre['date_creation_offre'], ENT_XML1);
    
    $xml .= <<<EOF
    <url>
        <loc>https://stagelink.com/offres/$id</loc>
        <lastmod>$lastmod</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

EOF;
}

// Ajouter les entreprises
foreach ($entreprises as $entreprise) {
    $id = htmlspecialchars((string)$entreprise['Id_entreprise'], ENT_XML1);
    $lastmod = date('Y-m-d'); // Date actuelle
    
    $xml .= <<<EOF
    <url>
        <loc>https://stagelink.com/entreprises/$id</loc>
        <lastmod>$lastmod</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

EOF;
}

$xml .= <<<'EOF'
</urlset>
EOF;

// Écrire le fichier
$sitemapPath = __DIR__ . '/sitemap.xml';
if (file_put_contents($sitemapPath, $xml) !== false) {
    echo "✅ sitemap.xml généré avec succès (" . count($offres) . " offres + " . count($entreprises) . " entreprises)\n";
    echo "Fichier : $sitemapPath\n";
} else {
    die("❌ Erreur lors de l'écriture du fichier sitemap.xml\n");
}
