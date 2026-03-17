<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\BaseModel;
use PDO;

class OffreModel extends BaseModel
{
    protected string $table = 'OFFRE';
    protected string $primaryKey = 'Id_offre';

    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['titre'])) {
            $where[]          = 'o.Titre LIKE :titre';
            $params[':titre'] = '%' . $filters['titre'] . '%';
        }

        if (!empty($filters['competence'])) {
            $where[]               = 'c.Libelle LIKE :competence';
            $params[':competence'] = '%' . $filters['competence'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = $this->getOffset($page, $perPage);

        $sql = "
            SELECT DISTINCT o.*, e.Nom AS raison_sociale,
                   COUNT(DISTINCT p.Id_etudiant) AS nb_candidatures
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.Id_entreprise = e.Id_entreprise
            LEFT JOIN REQUIERT r ON o.Id_offre = r.Id_offre
            LEFT JOIN COMPETENCE c ON r.Id_competence = c.Id_competence
            LEFT JOIN POSTULE p ON o.Id_offre = p.Id_offre
            WHERE $whereSQL
            GROUP BY o.Id_offre
            ORDER BY o.Date_offre DESC
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
            SELECT o.*, e.Nom AS raison_sociale,
                   COUNT(DISTINCT p.Id_etudiant) AS nb_candidatures
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.Id_entreprise = e.Id_entreprise
            LEFT JOIN POSTULE p ON o.Id_offre = p.Id_offre
            WHERE o.Id_offre = :id
            GROUP BY o.Id_offre
        ");
        $stmt->execute([':id' => $id]);
        $offre = $stmt->fetch();

        if ($offre) {
            $offre['competences'] = $this->getCompetences($id);
        }
        return $offre;
    }

    public function getCompetences(int $idOffre): array
    {
        $stmt = $this->db->prepare("
            SELECT c.* FROM COMPETENCE c
            JOIN REQUIERT r ON c.Id_competence = r.Id_competence
            WHERE r.Id_offre = :id
        ");
        $stmt->execute([':id' => $idOffre]);
        return $stmt->fetchAll();
    }

    public function create(array $data, array $competences = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO OFFRE (Titre, Description, Base_remuneration, Date_offre, Id_entreprise)
            VALUES (:titre, :description, :remuneration, :date_offre, :id_entreprise)
        ");
        $stmt->execute([
            ':titre'         => $data['Titre'],
            ':description'   => $data['Description'],
            ':remuneration'  => $data['Base_remuneration'],
            ':date_offre'    => $data['Date_offre'],
            ':id_entreprise' => $data['Id_entreprise'],
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->syncCompetences($id, $competences);
        return $id;
    }

    public function update(int $id, array $data, array $competences = []): bool
    {
        $stmt = $this->db->prepare("
            UPDATE OFFRE SET
                Titre = :titre,
                Description = :description,
                Base_remuneration = :remuneration,
                Date_offre = :date_offre,
                Id_entreprise = :id_entreprise
            WHERE Id_offre = :id
        ");
        $result = $stmt->execute([
            ':titre'         => $data['Titre'],
            ':description'   => $data['Description'],
            ':remuneration'  => $data['Base_remuneration'],
            ':date_offre'    => $data['Date_offre'],
            ':id_entreprise' => $data['Id_entreprise'],
            ':id'            => $id,
        ]);
        $this->syncCompetences($id, $competences);
        return $result;
    }

    private function syncCompetences(int $idOffre, array $competences): void
    {
        $this->db->prepare("DELETE FROM REQUIERT WHERE Id_offre = :id")
                 ->execute([':id' => $idOffre]);

        if (empty($competences)) return;

        $stmt = $this->db->prepare(
            "INSERT INTO REQUIERT (Id_offre, Id_competence) VALUES (:offre, :competence)"
        );
        foreach ($competences as $idComp) {
            $stmt->execute([':offre' => $idOffre, ':competence' => $idComp]);
        }
    }

    public function getStatistiques(): array
    {
        $total = $this->count();

        $stmt = $this->db->query("
            SELECT AVG(nb) FROM (
                SELECT COUNT(*) AS nb FROM POSTULE GROUP BY Id_offre
            ) t
        ");
        $moyenneCandidatures = round((float) $stmt->fetchColumn(), 1);

        $stmt = $this->db->query("
            SELECT COUNT(*) AS nb, o.Id_offre, o.Titre
            FROM WISH_LIST wl
            JOIN POSSEDE_WISH pw ON wl.Id_wishlist = pw.Id_wishlist
            JOIN OFFRE o ON pw.Id_offre = o.Id_offre
            GROUP BY o.Id_offre
            ORDER BY nb DESC
            LIMIT 5
        ");
        $topWishlist = $stmt->fetchAll();

        return compact('total', 'moyenneCandidatures', 'topWishlist');
    }
}
