<?php
declare(strict_types=1);

return [
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'dbname'   => $_ENV['DB_NAME']     ?? 'site_stages',
    'user'     => $_ENV['DB_USER']     ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
];
