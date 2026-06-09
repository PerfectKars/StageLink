<?php
$envFile = '/home/wsl/StageLink-1.7.0/.env';
echo "Fichier existe: " . (file_exists($envFile) ? "OUI" : "NON") . "<br>";
echo "Lisible: " . (is_readable($envFile) ? "OUI" : "NON") . "<br><br>";

if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NON DEFINI') . "<br>";
echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? 'NON DEFINI') . "<br>";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NON DEFINI') . "<br>";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'NON DEFINI') . "<br>";
echo "DB_PASS: " . ($_ENV['DB_PASS'] ?? 'NON DEFINI') . "<br>";
