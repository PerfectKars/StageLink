<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class EntrepriseModel extends BaseModel
{
    protected string $table = 'ENTREPRISE';
    protected string $primaryKey = 'Id_entreprise';

    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['nom'])) {
            $where[]        = 'e.Nom LIKE :nom';
            $params[':nom'] = '%' . $filters['nom'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = $this->getOffset($page, $perPage);

        $sql = "
            SELECT e.*,
                   AVG(ev.Note) AS moyenne_note,
                   COUNT(DISTINCT p.Id_etudiant) AS nb_stagiaires
            FROM ENTREPRISE e
            LEFT JOIN EVALUE ev ON e.Id_entreprise = ev.Id_entreprise
            LEFT JOIN OFFRE o ON e.Id_entreprise = o.Id_entreprise
            LEFT JOIN POSTULE p ON o.Id_offre = p.Id_offre
            WHERE $whereSQL
            GROUP BY e.Id_entreprise
            ORDER BY e.Nom ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT e.*, AVG(ev.Note) AS moyenne_note
            FROM ENTREPRISE e
            LEFT JOIN EVALUE ev ON e.Id_entreprise = ev.Id_entreprise
            WHERE e.Id_entreprise = :id
            GROUP BY e.Id_entreprise
        ");
        $stmt->execute([':id' => $id]);
        $entreprise = $stmt->fetch();

        if ($entreprise) {
            $entreprise['offres']      = $this->getOffres($id);
            $entreprise['evaluations'] = $this->getEvaluations($id);
        }
        return $entreprise;
    }

    public function getOffres(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM OFFRE
            WHERE Id_entreprise = :id
            ORDER BY Date_offre DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    public function getEvaluations(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT ev.*, e.Nom, e.Prenom
            FROM EVALUE ev
            JOIN ETUDIANT e ON ev.Id_etudiant = e.Id_etudiant
            WHERE ev.Id_entreprise = :id
            ORDER BY ev.Date_evaluation DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ENTREPRISE (Nom, Description, Email_contact, Tel_contact)
            VALUES (:nom, :description, :email, :tel)
        ");
        $stmt->execute([
            ':nom'         => $data['Nom'],
            ':description' => $data['Description'],
            ':email'       => $data['Email_contact'],
            ':tel'         => $data['Tel_contact'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE ENTREPRISE SET
                Nom           = :nom,
                Description   = :description,
                Email_contact = :email,
                Tel_contact   = :tel
            WHERE Id_entreprise = :id
        ");
        return $stmt->execute([
            ':nom'         => $data['Nom'],
            ':description' => $data['Description'],
            ':email'       => $data['Email_contact'],
            ':tel'         => $data['Tel_contact'],
            ':id'          => $id,
        ]);
    }

    public function noter(int $idEntreprise, int $idEtudiant, int $note, string $commentaire = ''): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO EVALUE (Id_entreprise, Id_etudiant, Note, Commentaire, Date_evaluation)
            VALUES (:entreprise, :etudiant, :note, :commentaire, CURDATE())
            ON DUPLICATE KEY UPDATE Note = :note, Commentaire = :commentaire
        ");
        return $stmt->execute([
            ':entreprise'  => $idEntreprise,
            ':etudiant'    => $idEtudiant,
            ':note'        => $note,
            ':commentaire' => $commentaire,
        ]);
    }
}
