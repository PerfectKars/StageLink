<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class OffreModel extends BaseModel
{
    protected string $table      = 'OFFRE';
    protected string $primaryKey = 'Id_offre';

    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ["o.statut = 'active'"];
        $params = [];

        if (!empty($filters['titre'])) {
            $where[]          = "o.titre LIKE :titre";
            $params[':titre'] = '%' . $filters['titre'] . '%';
        }
        if (!empty($filters['ville'])) {
            $where[]          = "se.Ville LIKE :ville";
            $params[':ville'] = '%' . $filters['ville'] . '%';
        }
        if (!empty($filters['competence'])) {
            $where[]               = "c.Libelle LIKE :competence";
            $params[':competence'] = '%' . $filters['competence'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset      = ($page - 1) * $perPage;

        $sql = "
            SELECT DISTINCT
                o.Id_offre,
                o.titre                   AS Titre,
                o.description             AS Description,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.date_creation_offre     AS Date_offre,
                o.statut,
                o.nb_candidatures,
                o.Id_site,
                e.Nom                     AS Nom_entreprise,
                e.Id_entreprise,
                e.SIRET,
                e.statut_juridique,
                se.Ville,
                se.Adresse,
                se.Code_postal
            FROM OFFRE o
            JOIN ENTREPRISE           e  ON e.Id_entreprise = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site      = o.Id_site
            LEFT JOIN REQUIERT        r  ON r.Id_offre      = o.Id_offre
            LEFT JOIN COMPETENCE      c  ON c.Id_competence = r.Id_competence
            WHERE {$whereClause}
            ORDER BY o.date_creation_offre DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM OFFRE WHERE statut = 'active'")->fetchColumn();
    }

    public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT
                o.Id_offre,
                o.titre                   AS Titre,
                o.description             AS Description,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.date_creation_offre     AS Date_offre,
                o.statut,
                o.nb_candidatures,
                o.date_prevue,
                o.Id_entreprise,
                o.Id_site,
                e.Nom                     AS Nom_entreprise,
                e.Description             AS Description_entreprise,
                e.Email_contact,
                e.Tel_contact,
                e.SIRET,
                e.statut_juridique,
                se.Ville,
                se.Adresse,
                se.Code_postal,
                se.Pays
            FROM OFFRE o
            JOIN ENTREPRISE           e  ON e.Id_entreprise = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site      = o.Id_site
            WHERE o.Id_offre = :id
        ");
        $stmt->execute([':id' => $id]);
        $offre = $stmt->fetch();
        if (!$offre) return false;

        $stmt = $this->db->prepare("
            SELECT c.Id_competence, c.Libelle AS Nom_competence
            FROM COMPETENCE c
            JOIN REQUIERT r ON r.Id_competence = c.Id_competence
            WHERE r.Id_offre = :id
        ");
        $stmt->execute([':id' => $id]);
        $offre['competences'] = $stmt->fetchAll();
        return $offre;
    }

    public function create(array $data, array $competenceIds = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO OFFRE
                (Id_entreprise, Id_site, titre, description,
                 gratification_par_heure, duree_mois, date_creation_offre, statut)
            VALUES (:entreprise, :site, :titre, :description,
                    :remuneration, :duree, :date, 'active')
        ");
        $stmt->execute([
            ':entreprise'  => $data['Id_entreprise'],
            ':site'        => $data['Id_site'] ?: null,
            ':titre'       => $data['Titre'],
            ':description' => $data['Description'],
            ':remuneration'=> $data['Base_remuneration'] ?: null,
            ':duree'       => $data['duree_mois'] ?: null,
            ':date'        => $data['Date_offre'] ?? date('Y-m-d'),
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->syncCompetences($id, $competenceIds);
        return $id;
    }

    public function update(int $id, array $data, array $competenceIds = []): void
    {
        $stmt = $this->db->prepare("
            UPDATE OFFRE SET
                Id_entreprise           = :entreprise,
                Id_site                 = :site,
                titre                   = :titre,
                description             = :description,
                gratification_par_heure = :remuneration,
                duree_mois              = :duree,
                date_creation_offre     = :date
            WHERE Id_offre = :id
        ");
        $stmt->execute([
            ':entreprise'  => $data['Id_entreprise'],
            ':site'        => $data['Id_site'] ?: null,
            ':titre'       => $data['Titre'],
            ':description' => $data['Description'],
            ':remuneration'=> $data['Base_remuneration'] ?: null,
            ':duree'       => $data['duree_mois'] ?: null,
            ':date'        => $data['Date_offre'] ?? date('Y-m-d'),
            ':id'          => $id,
        ]);
        $this->syncCompetences($id, $competenceIds);
    }

    public function deleteById(int $id): bool
    {
        return $this->db->prepare("DELETE FROM OFFRE WHERE Id_offre = :id")
                        ->execute([':id' => $id]);
    }

    public function findAllCompetences(): array
    {
        return $this->db->query(
            "SELECT Id_competence, Libelle AS Nom_competence FROM COMPETENCE ORDER BY Libelle"
        )->fetchAll();
    }

    public function getStatistiques(): array
    {
        $stats = [];
        $stats['total']   = (int) $this->db->query("SELECT COUNT(*) FROM OFFRE")->fetchColumn();
        $stats['actives'] = (int) $this->db->query("SELECT COUNT(*) FROM OFFRE WHERE statut = 'active'")->fetchColumn();
        $stats['par_entreprise'] = $this->db->query("
            SELECT e.Nom, COUNT(o.Id_offre) AS nb
            FROM ENTREPRISE e LEFT JOIN OFFRE o ON o.Id_entreprise = e.Id_entreprise
            GROUP BY e.Id_entreprise, e.Nom ORDER BY nb DESC
        ")->fetchAll();
        $stats['par_competence'] = $this->db->query("
            SELECT c.Libelle, COUNT(r.Id_offre) AS nb
            FROM COMPETENCE c LEFT JOIN REQUIERT r ON r.Id_competence = c.Id_competence
            GROUP BY c.Id_competence, c.Libelle ORDER BY nb DESC LIMIT 10
        ")->fetchAll();
        return $stats;
    }

    private function syncCompetences(int $idOffre, array $competenceIds): void
    {
        $this->db->prepare("DELETE FROM REQUIERT WHERE Id_offre = :id")->execute([':id' => $idOffre]);
        if (empty($competenceIds)) return;
        $stmt = $this->db->prepare("INSERT INTO REQUIERT (Id_offre, Id_competence) VALUES (:offre, :comp)");
        foreach ($competenceIds as $idComp) {
            $idComp = (int) $idComp;
            if ($idComp > 0) $stmt->execute([':offre' => $idOffre, ':comp' => $idComp]);
        }
    }
}
