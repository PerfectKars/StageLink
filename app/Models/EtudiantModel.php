<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class EtudiantModel extends BaseModel
{
    protected string $table      = 'ETUDIANT';
    protected string $primaryKey = 'Id_etudiant';

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ETUDIANT")->fetchColumn();
    }

    public function countSearch(string $search = ''): int
{
    if (empty($search)) return $this->count();

    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM ETUDIANT et
        JOIN UTILISATEUR u ON u.Id_utilisateur = et.Id_utilisateur
        WHERE (et.nom LIKE :s OR et.prenom LIKE :s OR u.Email LIKE :s)
    ");
    $stmt->bindValue(':s', '%' . $search . '%');
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

    public function search(string $search = '', int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = '1=1';
        $params = [];

        if (!empty($search)) {
            $where            = "(et.nom LIKE :s OR et.prenom LIKE :s OR u.Email LIKE :s)";
            $params[':s']     = '%' . $search . '%';
        }

        $stmt = $this->db->prepare("
            SELECT et.Id_etudiant, et.nom, et.prenom, et.Telephone,
                   et.Statut_recherche, u.Email, u.date_creation,
                   GROUP_CONCAT(pr.Libelle SEPARATOR ', ') AS promotions,
                   COUNT(DISTINCT p.Id_offre) AS nb_candidatures
            FROM ETUDIANT et
            JOIN UTILISATEUR u      ON u.Id_utilisateur  = et.Id_utilisateur
            LEFT JOIN APPARTIENT ap ON ap.Id_etudiant    = et.Id_etudiant
            LEFT JOIN PROMOTION pr  ON pr.Id_promotion   = ap.Id_promotion
            LEFT JOIN POSTULE p     ON p.Id_etudiant     = et.Id_etudiant
            WHERE {$where}
            GROUP BY et.Id_etudiant
            ORDER BY et.nom, et.prenom
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT et.Id_etudiant, et.nom, et.prenom, et.Telephone,
                   et.Statut_recherche, u.Email, u.date_creation,
                   GROUP_CONCAT(pr.Libelle SEPARATOR ', ') AS promotions
            FROM ETUDIANT et
            JOIN UTILISATEUR u      ON u.Id_utilisateur = et.Id_utilisateur
            LEFT JOIN APPARTIENT ap ON ap.Id_etudiant   = et.Id_etudiant
            LEFT JOIN PROMOTION pr  ON pr.Id_promotion  = ap.Id_promotion
            WHERE et.Id_etudiant = :id
            GROUP BY et.Id_etudiant
        ");
        $stmt->execute([':id' => $id]);
        $etudiant = $stmt->fetch();
        if (!$etudiant) return false;

        // Candidatures
        $stmt = $this->db->prepare("
            SELECT p.Id_offre, p.Date_candidature, p.Statut,
                   o.titre AS Titre, e.Nom AS Nom_entreprise,
                   cv.Nom_fichier AS cv_nom, cv.Chemin_fichier AS cv_chemin
            FROM POSTULE p
            JOIN OFFRE o      ON o.Id_offre       = p.Id_offre
            JOIN ENTREPRISE e ON e.Id_entreprise  = o.Id_entreprise
            LEFT JOIN CV cv   ON cv.Id_cv         = p.Id_cv
            WHERE p.Id_etudiant = :id
            ORDER BY p.Date_candidature DESC
        ");
        $stmt->execute([':id' => $id]);
        $etudiant['candidatures'] = $stmt->fetchAll();

        // CVs
        $stmt = $this->db->prepare("
            SELECT Id_cv, Nom_fichier, Date_depot, Cv_principal, Chemin_fichier
            FROM CV WHERE Id_etudiant = :id
            ORDER BY Cv_principal DESC, Date_depot DESC
        ");
        $stmt->execute([':id' => $id]);
        $etudiant['cvs'] = $stmt->fetchAll();

        return $etudiant;
    }

    public function getPromotions(): array
    {
        return $this->db->query(
            "SELECT Id_promotion, Libelle, Annee, Filiere FROM PROMOTION ORDER BY Annee DESC, Libelle"
        )->fetchAll();
    }

    public function createEtudiant(array $data): int
    {
        // UTILISATEUR
        $stmt = $this->db->prepare("
            INSERT INTO UTILISATEUR (Email, Mot_de_passe, Role)
            VALUES (:email, :mdp, 'etudiant')
        ");
        $stmt->execute([
            ':email' => $data['email'],
            ':mdp'   => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
        ]);
        $idUtilisateur = (int) $this->db->lastInsertId();

        // ETUDIANT
        $stmt = $this->db->prepare("
            INSERT INTO ETUDIANT (nom, prenom, Telephone, Statut_recherche, Id_utilisateur)
            VALUES (:nom, :prenom, :tel, :statut, :id_u)
        ");
        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':tel'    => $data['telephone'] ?: null,
            ':statut' => $data['statut_recherche'] ?: 'En recherche',
            ':id_u'   => $idUtilisateur,
        ]);
        $idEtudiant = (int) $this->db->lastInsertId();

        // APPARTIENT (promotion)
        if (!empty($data['id_promotion'])) {
            $this->db->prepare("
                INSERT INTO APPARTIENT (Id_etudiant, Id_promotion, date_debut)
                VALUES (:et, :pr, CURDATE())
            ")->execute([':et' => $idEtudiant, ':pr' => $data['id_promotion']]);
        }

        return $idEtudiant;
    }

    public function updateEtudiant(int $idEtudiant, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE ETUDIANT SET nom = :nom, prenom = :prenom,
                   Telephone = :tel, Statut_recherche = :statut
            WHERE Id_etudiant = :id
        ");
        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':tel'    => $data['telephone'] ?: null,
            ':statut' => $data['statut_recherche'] ?: null,
            ':id'     => $idEtudiant,
        ]);

        // Email
        $this->db->prepare("
            UPDATE UTILISATEUR u
            JOIN ETUDIANT e ON e.Id_utilisateur = u.Id_utilisateur
            SET u.Email = :email WHERE e.Id_etudiant = :id
        ")->execute([':email' => $data['email'], ':id' => $idEtudiant]);

        // Mot de passe si fourni
        if (!empty($data['mot_de_passe'])) {
            $this->db->prepare("
                UPDATE UTILISATEUR u
                JOIN ETUDIANT e ON e.Id_utilisateur = u.Id_utilisateur
                SET u.Mot_de_passe = :mdp WHERE e.Id_etudiant = :id
            ")->execute([
                ':mdp' => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
                ':id'  => $idEtudiant,
            ]);
        }
    }

    public function deleteEtudiant(int $idEtudiant): void
    {
        $this->db->prepare("
            DELETE u FROM UTILISATEUR u
            JOIN ETUDIANT e ON e.Id_utilisateur = u.Id_utilisateur
            WHERE e.Id_etudiant = :id
        ")->execute([':id' => $idEtudiant]);
    }

    public function updatePhoto(int $idEtudiant, string $nomFichier): void
{
    $this->db->prepare(
        "UPDATE ETUDIANT SET photo = :photo WHERE Id_etudiant = :id"
    )->execute([':photo' => $nomFichier, ':id' => $idEtudiant]);
}

public function getPhoto(int $idEtudiant): string|null
{
    $stmt = $this->db->prepare(
        "SELECT photo FROM ETUDIANT WHERE Id_etudiant = :id"
    );
    $stmt->execute([':id' => $idEtudiant]);
    return $stmt->fetchColumn() ?: null;
}
}
