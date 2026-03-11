<?php
// router.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si la requête correspond à un fichier réel dans public/, on le sert directement
$publicPath = __DIR__ . $uri;
if (file_exists($publicPath)) {
    return false; // laisse PHP gérer le fichier statique
}

// Sinon, on regarde si le fichier existe dans ../pages/
$pagePath = __DIR__ . '/../pages' . $uri;
if (file_exists($pagePath)) {
    require $pagePath;
    return;
}

// Sinon, on tombe sur index.php
require __DIR__ . '/index.php';
