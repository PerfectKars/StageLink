<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class EntrepriseModel extends BaseModel
{
    protected string $table = 'ENTREPRISE';
    protected string $primaryKey = 'id_entreprise';

    /**
     * Recherche des entreprises avec filtres et pagination
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['nom'])) {
            $where[]       = 'raison_sociale LIKE :nom';
            $params[':nom'] = '%' . $filters['nom'] . '%';
        }

        if (!empty($filters['secteur'])) {
            $where[]           = 'secteur_activite LIKE :secteur';
            $params[':secteur'] = '%' . $filters['secteur'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = $this->getOffset($page, $perPage);

        $sql = "
            SELECT e.*,
                   AVG(ev.note) AS moyenne_note,
                   COUNT(DISTINCT p.id_etudiant) AS nb_stagiaires
            FROM ENTREPRISE e
            LEFT JOIN EVALUATION ev ON e.id_entreprise = ev.id_entreprise
            LEFT JOIN OFFRE o ON e.id_entreprise = o.id_entreprise
            LEFT JOIN POSTULER p ON o.id_offre = p.id_offre
            WHERE $whereSQL
            GROUP BY e.id_entreprise
            ORDER BY e.raison_sociale ASC
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
     * Entreprise complète avec offres et évaluations
     */
    public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT e.*, AVG(ev.note) AS moyenne_note
            FROM ENTREPRISE e
            LEFT JOIN EVALUATION ev ON e.id_entreprise = ev.id_entreprise
            WHERE e.id_entreprise = :id
            GROUP BY e.id_entreprise
        ");
        $stmt->execute([':id' => $id]);
        $entreprise = $stmt->fetch();

        if ($entreprise) {
            $entreprise['offres']      = $this->getOffres($id);
            $entreprise['evaluations'] = $this->getEvaluations($id);
        }

        return $entreprise;
    }

    /**
     * Offres d'une entreprise
     */
    public function getOffres(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM OFFRE
            WHERE id_entreprise = :id
            ORDER BY date_publication DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    /**
     * Évaluations d'une entreprise
     */
    public function getEvaluations(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT ev.*, e.nom, e.prenom
            FROM EVALUATION ev
            JOIN ETUDIANT e ON ev.id_etudiant = e.id_etudiant
            WHERE ev.id_entreprise = :id
            ORDER BY ev.date_evaluation DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    /**
     * Crée une entreprise
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ENTREPRISE
                (raison_sociale, siret, secteur_activite, description, adresse_siege, site_web)
            VALUES
                (:nom, :siret, :secteur, :description, :adresse, :site_web)
        ");

        $stmt->execute([
            ':nom'         => $data['raison_sociale'],
            ':siret'       => $data['siret'] ?? null,
            ':secteur'     => $data['secteur_activite'],
            ':description' => $data['description'],
            ':adresse'     => $data['adresse_siege'],
            ':site_web'    => $data['site_web'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour une entreprise
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE ENTREPRISE SET
                raison_sociale  = :nom,
                siret           = :siret,
                secteur_activite= :secteur,
                description     = :description,
                adresse_siege   = :adresse,
                site_web        = :site_web
            WHERE id_entreprise = :id
        ");

        return $stmt->execute([
            ':nom'         => $data['raison_sociale'],
            ':siret'       => $data['siret'] ?? null,
            ':secteur'     => $data['secteur_activite'],
            ':description' => $data['description'],
            ':adresse'     => $data['adresse_siege'],
            ':site_web'    => $data['site_web'] ?? null,
            ':id'          => $id,
        ]);
    }

    /**
     * Ajoute une évaluation (SFx 5)
     */
    public function noter(int $idEntreprise, int $idEtudiant, int $note, string $commentaire = ''): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO EVALUATION (id_entreprise, id_etudiant, note, commentaire)
            VALUES (:entreprise, :etudiant, :note, :commentaire)
            ON DUPLICATE KEY UPDATE note = :note, commentaire = :commentaire
        ");

        return $stmt->execute([
            ':entreprise'   => $idEntreprise,
            ':etudiant'     => $idEtudiant,
            ':note'         => $note,
            ':commentaire'  => $commentaire,
        ]);
    }
}
