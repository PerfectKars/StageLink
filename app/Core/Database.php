<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {

            $config = require ROOT_PATH . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['dbname']
            );

            try {
    self::$instance = new PDO(
        "mysql:host=gondola.proxy.rlwy.net;port=33783;dbname=railway;charset=utf8mb4",
        "root",
        "mreFNilXZGCVkKaZCwSRCwTtetCTjPYQ",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die($e->getMessage());
}
        }

        return self::$instance;
    }
}