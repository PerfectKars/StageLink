<?php
/**
 * bootstrap-tests.php
 * Bootstrap PHPUnit — charge l'autoloader sans exécuter le routeur
 */

declare(strict_types=1);

// Charger Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Charger les variables d'environnement depuis .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Démarrer la session pour les tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir une variable de test pour éviter les erreurs de routage
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
