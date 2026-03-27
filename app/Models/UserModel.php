<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class UserModel extends BaseModel
{
    protected string $table      = 'UTILISATEUR';
    protected string $primaryKey = 'Id_utilisateur';

    /**
     * Trouve un utilisateur par ID et enrichit avec nom/prenom selon le rôle.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM UTILISATEUR WHERE Id_utilisateur = :id"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user) return false;

        $user = $this->enrichUser($user);
        return $user;
    }

    /**
     * Trouve un utilisateur par email (insensible à la casse).
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM UTILISATEUR WHERE Email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Authentifie un utilisateur, retourne le tableau enrichi ou false.
     */
    public function authenticate(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user['Mot_de_passe'])) {
            return false;
        }

        return $this->enrichUser($user);
    }

    /**
     * Enrichit un utilisateur avec nom/prénom/téléphone depuis la table de rôle.
     */
    private function enrichUser(array $user): array
    {
        $id   = (int) $user['Id_utilisateur'];
        $role = $user['Role'];

        if ($role === 'etudiant') {
            $stmt = $this->db->prepare(
                "SELECT nom, prenom, Telephone FROM ETUDIANT WHERE Id_utilisateur = :id"
            );
            $stmt->execute([':id' => $id]);
            $extra = $stmt->fetch();

        } elseif ($role === 'pilote') {
            $stmt = $this->db->prepare(
                "SELECT nom, prenom, Telephone FROM PILOTE WHERE Id_utilisateur = :id"
            );
            $stmt->execute([':id' => $id]);
            $extra = $stmt->fetch();

        } elseif ($role === 'admin') {
            $stmt = $this->db->prepare(
                "SELECT nom, prenom FROM ADMIN WHERE Id_utilisateur = :id"
            );
            $stmt->execute([':id' => $id]);
            $extra = $stmt->fetch();
        }

        if (!empty($extra)) {
            $user['nom']       = $extra['nom']       ?? '';
            $user['prenom']    = $extra['prenom']     ?? '';
            $user['telephone'] = $extra['Telephone']  ?? '';
        } else {
            $user['nom']       = '';
            $user['prenom']    = '';
            $user['telephone'] = '';
        }

        return $user;
    }

    /**
     * Crée un utilisateur + son entrée dans la table de rôle.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO UTILISATEUR (Email, Mot_de_passe, Role)
            VALUES (:email, :mdp, :role)
        ");
        $stmt->execute([
            ':email' => $data['email'],
            ':mdp'   => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
            ':role'  => $data['role'] ?? 'etudiant',
        ]);
        $idUtilisateur = (int) $this->db->lastInsertId();

        // Créer l'entrée dans la table de rôle
        if (($data['role'] ?? 'etudiant') === 'etudiant') {
            $stmt = $this->db->prepare("
                INSERT INTO ETUDIANT (nom, prenom, Telephone, Statut_recherche, Id_utilisateur)
                VALUES (:nom, :prenom, :tel, 'En recherche', :id_u)
            ");
            $stmt->execute([
                ':nom'    => $data['nom']       ?? '',
                ':prenom' => $data['prenom']    ?? '',
                ':tel'    => $data['telephone'] ?? null,
                ':id_u'   => $idUtilisateur,
            ]);
        } elseif (($data['role'] ?? '') === 'pilote') {
            $stmt = $this->db->prepare("
                INSERT INTO PILOTE (nom, prenom, Telephone, Id_utilisateur)
                VALUES (:nom, :prenom, :tel, :id_u)
            ");
            $stmt->execute([
                ':nom'    => $data['nom']       ?? '',
                ':prenom' => $data['prenom']    ?? '',
                ':tel'    => $data['telephone'] ?? null,
                ':id_u'   => $idUtilisateur,
            ]);
        }

        return $idUtilisateur;
    }

    /**
     * Met à jour les infos d'un utilisateur dans sa table de rôle.
     */
    public function update(int $id, array $data): bool
    {
        // Récupère le rôle
        $user = $this->findById($id);
        if (!$user) return false;

        $role = $user['Role'];

        if ($role === 'etudiant') {
            $this->db->prepare("
                UPDATE ETUDIANT SET nom = :nom, prenom = :prenom, Telephone = :tel
                WHERE Id_utilisateur = :id
            ")->execute([':nom' => $data['nom'], ':prenom' => $data['prenom'], ':tel' => $data['telephone'] ?? null, ':id' => $id]);

        } elseif ($role === 'pilote') {
            $this->db->prepare("
                UPDATE PILOTE SET nom = :nom, prenom = :prenom, Telephone = :tel
                WHERE Id_utilisateur = :id
            ")->execute([':nom' => $data['nom'], ':prenom' => $data['prenom'], ':tel' => $data['telephone'] ?? null, ':id' => $id]);
        }

        // Mise à jour email / mdp dans UTILISATEUR
        if (!empty($data['email'])) {
            $this->db->prepare("UPDATE UTILISATEUR SET Email = :email WHERE Id_utilisateur = :id")
                     ->execute([':email' => $data['email'], ':id' => $id]);
        }
        if (!empty($data['mot_de_passe'])) {
            $this->db->prepare("UPDATE UTILISATEUR SET Mot_de_passe = :mdp WHERE Id_utilisateur = :id")
                     ->execute([':mdp' => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT), ':id' => $id]);
        }

        return true;
    }

    /**
     * Recherche des utilisateurs par rôle.
     */
    public function findByRole(string $role, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        if ($role === 'etudiant') {
            $sql = "
                SELECT u.Id_utilisateur, e.nom, e.prenom, u.Email, e.Telephone, u.Role
                FROM UTILISATEUR u
                JOIN ETUDIANT e ON e.Id_utilisateur = u.Id_utilisateur
                WHERE u.Role = 'etudiant'
                ORDER BY e.nom, e.prenom
                LIMIT :limit OFFSET :offset
            ";
        } elseif ($role === 'pilote') {
            $sql = "
                SELECT u.Id_utilisateur, p.nom, p.prenom, u.Email, p.Telephone, u.Role
                FROM UTILISATEUR u
                JOIN PILOTE p ON p.Id_utilisateur = u.Id_utilisateur
                WHERE u.Role = 'pilote'
                ORDER BY p.nom, p.prenom
                LIMIT :limit OFFSET :offset
            ";
        } else {
            $sql = "
                SELECT u.Id_utilisateur, a.nom, a.prenom, u.Email, u.Role
                FROM UTILISATEUR u
                JOIN ADMIN a ON a.Id_utilisateur = u.Id_utilisateur
                WHERE u.Role = 'admin'
                ORDER BY a.nom
                LIMIT :limit OFFSET :offset
            ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
