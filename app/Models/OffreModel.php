<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class OffreModel extends BaseModel
{
    protected string $table = 'OFFRE';
    protected string $primaryKey = 'id_offre';

    /**
     * Recherche des offres avec filtres et pagination
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['titre'])) {
            $where[]          = 'o.titre LIKE :titre';
            $params[':titre'] = '%' . $filters['titre'] . '%';
        }

        if (!empty($filters['ville'])) {
            $where[]          = 'o.ville_stage LIKE :ville';
            $params[':ville'] = '%' . $filters['ville'] . '%';
        }

        if (!empty($filters['competence'])) {
            $where[]               = 'c.libelle LIKE :competence';
            $params[':competence'] = '%' . $filters['competence'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = $this->getOffset($page, $perPage);

        $sql = "
            SELECT DISTINCT o.*, e.raison_sociale,
                   COUNT(DISTINCT p.id_etudiant) AS nb_candidatures
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            LEFT JOIN REQUERIR r ON o.id_offre = r.id_offre
            LEFT JOIN COMPETENCE c ON r.id_competence = c.id_competence
            LEFT JOIN POSTULER p ON o.id_offre = p.id_offre
            WHERE $whereSQL
            GROUP BY o.id_offre
            ORDER BY o.date_publication DESC
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

    /**
     * Récupère une offre complète avec entreprise et compétences
     */
    public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT o.*, e.raison_sociale, e.logo_url,
                   COUNT(DISTINCT p.id_etudiant) AS nb_candidatures
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            LEFT JOIN POSTULER p ON o.id_offre = p.id_offre
            WHERE o.id_offre = :id
            GROUP BY o.id_offre
        ");
        $stmt->execute([':id' => $id]);
        $offre = $stmt->fetch();

        if ($offre) {
            $offre['competences'] = $this->getCompetences($id);
        }

        return $offre;
    }

    /**
     * Compétences d'une offre
     */
    public function getCompetences(int $idOffre): array
    {
        $stmt = $this->db->prepare("
            SELECT c.* FROM COMPETENCE c
            JOIN REQUERIR r ON c.id_competence = r.id_competence
            WHERE r.id_offre = :id
        ");
        $stmt->execute([':id' => $idOffre]);
        return $stmt->fetchAll();
    }

    /**
     * Crée une offre et associe ses compétences
     */
    public function create(array $data, array $competences = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO OFFRE
                (titre, description_mission, date_debut, duree_mois, gratification,
                 ville_stage, teletravail, id_entreprise, id_type)
            VALUES
                (:titre, :description, :date_debut, :duree, :gratification,
                 :ville, :teletravail, :id_entreprise, :id_type)
        ");

        $stmt->execute([
            ':titre'         => $data['titre'],
            ':description'   => $data['description_mission'],
            ':date_debut'    => $data['date_debut'],
            ':duree'         => $data['duree_mois'],
            ':gratification' => $data['gratification'],
            ':ville'         => $data['ville_stage'],
            ':teletravail'   => $data['teletravail'] ?? 0,
            ':id_entreprise' => $data['id_entreprise'],
            ':id_type'       => $data['id_type'],
        ]);

        $id = (int) $this->db->lastInsertId();
        $this->syncCompetences($id, $competences);

        return $id;
    }

    /**
     * Met à jour une offre
     */
    public function update(int $id, array $data, array $competences = []): bool
    {
        $stmt = $this->db->prepare("
            UPDATE OFFRE SET
                titre = :titre,
                description_mission = :description,
                date_debut = :date_debut,
                duree_mois = :duree,
                gratification = :gratification,
                ville_stage = :ville,
                teletravail = :teletravail,
                id_entreprise = :id_entreprise,
                id_type = :id_type
            WHERE id_offre = :id
        ");

        $result = $stmt->execute([
            ':titre'         => $data['titre'],
            ':description'   => $data['description_mission'],
            ':date_debut'    => $data['date_debut'],
            ':duree'         => $data['duree_mois'],
            ':gratification' => $data['gratification'],
            ':ville'         => $data['ville_stage'],
            ':teletravail'   => $data['teletravail'] ?? 0,
            ':id_entreprise' => $data['id_entreprise'],
            ':id_type'       => $data['id_type'],
            ':id'            => $id,
        ]);

        $this->syncCompetences($id, $competences);

        return $result;
    }

    /**
     * Synchronise les compétences d'une offre
     */
    private function syncCompetences(int $idOffre, array $competences): void
    {
        $this->db->prepare("DELETE FROM REQUERIR WHERE id_offre = :id")
                 ->execute([':id' => $idOffre]);

        if (empty($competences)) {
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO REQUERIR (id_offre, id_competence) VALUES (:offre, :competence)"
        );

        foreach ($competences as $idComp) {
            $stmt->execute([':offre' => $idOffre, ':competence' => $idComp]);
        }
    }

    /**
     * Statistiques des offres (SFx 11)
     */
    public function getStatistiques(): array
    {
        $total = $this->count();

        $stmt = $this->db->query("SELECT AVG(nb) FROM (
            SELECT COUNT(*) AS nb FROM POSTULER GROUP BY id_offre
        ) t");
        $moyenneCandidatures = round((float) $stmt->fetchColumn(), 1);

        $stmt = $this->db->query("
            SELECT duree_mois, COUNT(*) AS nb
            FROM OFFRE
            GROUP BY duree_mois
            ORDER BY duree_mois
        ");
        $repartitionDuree = $stmt->fetchAll();

        $stmt = $this->db->query("
            SELECT o.id_offre, o.titre, COUNT(w.id_etudiant) AS nb_wishlist
            FROM OFFRE o
            JOIN WISHLIST w ON o.id_offre = w.id_offre
            GROUP BY o.id_offre
            ORDER BY nb_wishlist DESC
            LIMIT 5
        ");
        $topWishlist = $stmt->fetchAll();

        return compact('total', 'moyenneCandidatures', 'repartitionDuree', 'topWishlist');
    }
}
