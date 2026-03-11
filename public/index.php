<?php
declare(strict_types=1);

// ── Constantes globales ───────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL',  'http://stagelink.local');

// ── Autoloader PSR-4 ──────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    if (!str_starts_with($class, $prefix)) return;

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (file_exists($file)) require $file;
});

// ── Session sécurisée ─────────────────────────────────────────────────────────
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ── Token CSRF ────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Routeur ───────────────────────────────────────────────────────────────────
$router = require ROOT_PATH . '/config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
