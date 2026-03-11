<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class UserModel extends BaseModel
{
    protected string $table = 'UTILISATEUR';
    protected string $primaryKey = 'id_utilisateur';

    /**
     * Trouve un utilisateur par email
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM UTILISATEUR WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            return false;
        }

        return $user;
    }

    /**
     * Crée un utilisateur (étudiant ou pilote)
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe, role, telephone)
            VALUES (:nom, :prenom, :email, :mdp, :role, :tel)
        ");

        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email'  => $data['email'],
            ':mdp'    => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
            ':role'   => $data['role'] ?? 'etudiant',
            ':tel'    => $data['telephone'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(int $id, array $data): bool
    {
        $fields = ['nom = :nom', 'prenom = :prenom', 'email = :email', 'telephone = :tel'];
        $params = [
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email'  => $data['email'],
            ':tel'    => $data['telephone'] ?? null,
            ':id'     => $id,
        ];

        // Mise à jour du mot de passe seulement si fourni
        if (!empty($data['mot_de_passe'])) {
            $fields[]      = 'mot_de_passe = :mdp';
            $params[':mdp'] = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
        }

        $sql = 'UPDATE UTILISATEUR SET ' . implode(', ', $fields) . ' WHERE id_utilisateur = :id';

        return $this->db->prepare($sql)->execute($params);
    }

    /**
     * Recherche des utilisateurs par rôle
     */
    public function findByRole(string $role, int $page = 1, int $perPage = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT id_utilisateur, nom, prenom, email, telephone, role
            FROM UTILISATEUR
            WHERE role = :role
            ORDER BY nom, prenom
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $this->getOffset($page, $perPage), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
