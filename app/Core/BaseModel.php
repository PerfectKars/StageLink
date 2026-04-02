<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne la liste des colonnes de la table courante (avec cache statique)
     */
    protected function getTableColumns(): array
    {
        static $cache = [];

        if (isset($cache[$this->table])) {
            return $cache[$this->table];
        }

        $sql = "SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = :table_name
                ORDER BY ORDINAL_POSITION";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':table_name' => $this->table]);

        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // On peut exclure des colonnes sensibles ici si besoin plus tard
        return $cache[$this->table] = $columns;
    }

    /**
     * Récupère un enregistrement par son ID (sans SELECT *)
     */
    public function findById(int $id): array|false
    {
        $columns = implode(', ', $this->getTableColumns());
        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Récupère tous les enregistrements avec pagination (sans SELECT *)
     */
    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $columns = implode(', ', $this->getTableColumns());
        $sql = "SELECT {$columns} FROM {$this->table} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Méthodes utilitaires protégées
    protected function getOffset(int $page, int $perPage): int
    {
        return ($page - 1) * $perPage;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected function execute(string $sql, array $params = []): bool
    {
        return $this->db->prepare($sql)->execute($params);
    }
}