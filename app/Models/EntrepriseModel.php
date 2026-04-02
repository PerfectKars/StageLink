<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class EntrepriseModel extends BaseModel
{
    protected string $table      = 'ENTREPRISE';
    protected string $primaryKey = 'Id_entreprise';

    
    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
{
    $where  = ['1=1'];
    $params = [];

    if (!empty($filters['nom'])) {
        $where[]        = 'e.Nom LIKE :nom';
        $params[':nom'] = '%' . $filters['nom'] . '%';
    }
    if (!empty($filters['ville'])) {
        $where[]          = 'se.Ville LIKE :ville';
        $params[':ville'] = '%' . $filters['ville'] . '%';
    }

    $whereSQL = implode(' AND ', $where);
    $offset   = $this->getOffset($page, $perPage);

    $sql = "
        SELECT DISTINCT
            e.Id_entreprise, 
            e.Nom, 
            e.Description,
            e.Email_contact, 
            e.Tel_contact,
            e.statut_juridique, 
            e.SIRET,
            se.Ville,
            ROUND(AVG(ev.Note), 1)                  AS moyenne_note,
            COUNT(DISTINCT ev.Id_etudiant)          AS nb_evaluations,
            COUNT(DISTINCT p.Id_etudiant)           AS nb_stagiaires
        FROM ENTREPRISE e
        LEFT JOIN SITE_ENTREPRISE se ON se.Id_entreprise = e.Id_entreprise
        LEFT JOIN EVALUE ev          ON ev.Id_entreprise = e.Id_entreprise
        LEFT JOIN OFFRE o            ON o.Id_entreprise  = e.Id_entreprise
        LEFT JOIN POSTULE p          ON p.Id_offre       = o.Id_offre
        WHERE $whereSQL
        GROUP BY e.Id_entreprise, se.Ville
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
        SELECT 
            e.Id_entreprise,
            e.Nom,
            e.Description,
            e.Email_contact,
            e.Tel_contact,
            e.statut_juridique,
            e.SIRET,
            ROUND(AVG(ev.Note), 2)      AS moyenne_note,
            COUNT(ev.Id_etudiant)       AS nb_evaluations
        FROM ENTREPRISE e
        LEFT JOIN EVALUE ev ON e.Id_entreprise = ev.Id_entreprise
        WHERE e.Id_entreprise = :id
        GROUP BY e.Id_entreprise
    ");
    $stmt->execute([':id' => $id]);
    
    $entreprise = $stmt->fetch();

    if ($entreprise) {
        $entreprise['sites']       = $this->getSites($id);
        $entreprise['offres']      = $this->getOffres($id);
        $entreprise['evaluations'] = $this->getEvaluations($id);
    }

    return $entreprise;
}

    /**
     * Récupère tous les sites d'une entreprise.
     */
        public function getSites(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT Id_site, Id_entreprise, Adresse, Ville, Code_postal, Pays
            FROM SITE_ENTREPRISE
            WHERE Id_entreprise = :id
            ORDER BY Id_site ASC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les sites de toutes les entreprises (pour formulaire offre).
     * Retourne [Id_entreprise => [sites...]]
     */
    public function getAllSitesGrouped(): array
    {
        $stmt = $this->db->query("
            SELECT Id_site, Id_entreprise, Adresse, Ville, Code_postal, Pays
            FROM SITE_ENTREPRISE
            ORDER BY Id_entreprise, Ville
        ");
        $rows   = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['Id_entreprise']][] = $row;
        }
        return $result;
    }

        public function getOffres(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.Id_offre,
                o.Id_entreprise,
                o.Id_site,
                o.titre,
                o.description,
                o.statut,
                o.gratification_par_heure,
                o.duree_mois,
                o.date_creation_offre,
                o.date_prevue,
                se.Ville,
                se.Adresse
            FROM OFFRE o
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site = o.Id_site
            WHERE o.Id_entreprise = :id
            ORDER BY o.date_creation_offre DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    public function getEvaluations(int $idEntreprise): array
    {
        $stmt = $this->db->prepare("
            SELECT ev.*, e.nom, e.prenom
            FROM EVALUE ev
            JOIN ETUDIANT e ON ev.Id_etudiant = e.Id_etudiant
            WHERE ev.Id_entreprise = :id
            ORDER BY ev.Date_evaluation DESC
        ");
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    /**
     * Crée une entreprise avec SIRET et statut juridique.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ENTREPRISE
                (Nom, Description, Email_contact, Tel_contact, statut_juridique, SIRET)
            VALUES
                (:nom, :description, :email, :tel, :statut, :siret)
        ");
        $stmt->execute([
            ':nom'         => $data['Nom'],
            ':description' => $data['Description']      ?? null,
            ':email'       => $data['Email_contact']    ?? null,
            ':tel'         => $data['Tel_contact']      ?? null,
            ':statut'      => $data['statut_juridique'] ?? null,
            ':siret'       => $data['SIRET']            ?? null,
        ]);
        $id = (int) $this->db->lastInsertId();

        // Créer le site principal si fourni
        if (!empty($data['Adresse'])) {
            $this->addSite($id, [
                'Adresse'     => $data['Adresse'],
                'Ville'       => $data['Ville']       ?? '',
                'Code_postal' => $data['Code_postal'] ?? '',
                'Pays'        => $data['Pays']        ?? 'France',
            ]);
        }

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE ENTREPRISE SET
                Nom              = :nom,
                Description      = :description,
                Email_contact    = :email,
                Tel_contact      = :tel,
                statut_juridique = :statut,
                SIRET            = :siret
            WHERE Id_entreprise = :id
        ");
        return $stmt->execute([
            ':nom'         => $data['Nom'],
            ':description' => $data['Description']      ?? null,
            ':email'       => $data['Email_contact']    ?? null,
            ':tel'         => $data['Tel_contact']      ?? null,
            ':statut'      => $data['statut_juridique'] ?? null,
            ':siret'       => $data['SIRET']            ?? null,
            ':id'          => $id,
        ]);
    }

    /**
     * Ajoute un site à une entreprise.
     */
    public function addSite(int $idEntreprise, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO SITE_ENTREPRISE
                (Adresse, Ville, Code_postal, Pays, Id_entreprise)
            VALUES
                (:adresse, :ville, :cp, :pays, :id)
        ");
        $stmt->execute([
            ':adresse' => $data['Adresse'],
            ':ville'   => $data['Ville'],
            ':cp'      => $data['Code_postal'],
            ':pays'    => $data['Pays'] ?? 'France',
            ':id'      => $idEntreprise,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un site.
     */
    public function updateSite(int $idSite, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE SITE_ENTREPRISE SET
                Adresse     = :adresse,
                Ville       = :ville,
                Code_postal = :cp,
                Pays        = :pays
            WHERE Id_site = :id
        ");
        return $stmt->execute([
            ':adresse' => $data['Adresse'],
            ':ville'   => $data['Ville'],
            ':cp'      => $data['Code_postal'],
            ':pays'    => $data['Pays'] ?? 'France',
            ':id'      => $idSite,
        ]);
    }

    public function noter(int $idEntreprise, int $idEtudiant, int $note, string $commentaire = ''): bool
{
    // Debug 1 : on voit ce qu'on essaie d'insérer
    error_log("=== TENTATIVE NOTE === Id_entreprise=$idEntreprise | Id_etudiant=$idEtudiant | Note=$note | Commentaire=" . substr($commentaire, 0, 100));

    // Suppression ancienne évaluation
    $delete = $this->db->prepare("
        DELETE FROM EVALUE 
        WHERE Id_entreprise = :entreprise AND Id_etudiant = :etudiant
    ");
    $delete->execute([
        ':entreprise' => $idEntreprise,
        ':etudiant'   => $idEtudiant
    ]);

    // Insertion
    $stmt = $this->db->prepare("
        INSERT INTO EVALUE 
            (Id_entreprise, Id_etudiant, Note, Commentaire, Date_evaluation)
        VALUES 
            (:entreprise, :etudiant, :note, :commentaire, NOW())
    ");

    $success = $stmt->execute([
        ':entreprise'  => $idEntreprise,
        ':etudiant'    => $idEtudiant,
        ':note'        => min(5, max(1, $note)),
        ':commentaire' => trim($commentaire)
    ]);

    if (!$success) {
        // Debug 2 : on récupère l'erreur exacte
        $errorInfo = $stmt->errorInfo();
        error_log("ERREUR INSERT EVALUE - Code: " . $stmt->errorCode() . " | Info: " . print_r($errorInfo, true));
        
        // On force un message visible
        throw new \PDOException("Échec INSERT EVALUE - " . ($errorInfo[2] ?? 'Erreur inconnue'));
    }

    error_log("=== NOTE ENREGISTRÉE AVEC SUCCÈS ===");
    return true;
}

}
