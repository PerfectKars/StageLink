<?php
declare(strict_types=1);

// Chargement .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL',  'https://stagelink.local');

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    if (!str_starts_with($class, $prefix)) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Anti-cache pour les pages authentifiées
if (!empty($_SESSION['user'])) {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$router = require ROOT_PATH . '/config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
