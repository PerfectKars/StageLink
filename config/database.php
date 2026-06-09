<?php
return [
    'host' => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? null,
    'port' => $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? null,
    'dbname' => $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? null,
    'user' => $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? null,
    'password' => $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? null
];
