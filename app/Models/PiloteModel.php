<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;

class PiloteModel extends BaseModel
{
    protected string $table      = 'PILOTE';
    protected string $primaryKey = 'Id_pilote';

    /**
     * Promotions du pilote connecté.
     */
    public function getPromotions(int $idUtilisateur, int $limit = 999, int $offset = 0): array
{
    $stmt = $this->db->prepare("
        SELECT pr.Id_promotion, pr.Libelle, pr.Annee, pr.Filiere,
               COUNT(ap.Id_etudiant) AS nb_etudiants
        FROM PROMOTION pr
        JOIN PILOTE p ON p.Id_pilote = pr.Id_pilote
        LEFT JOIN APPARTIENT ap ON ap.Id_promotion = pr.Id_promotion
        WHERE p.Id_utilisateur = :id
        GROUP BY pr.Id_promotion
        ORDER BY pr.Annee DESC, pr.Libelle
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':id', $idUtilisateur, \PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

public function countPromotions(int $idUtilisateur): int
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM PROMOTION pr
        JOIN PILOTE p ON p.Id_pilote = pr.Id_pilote
        WHERE p.Id_utilisateur = :id
    ");
    $stmt->bindValue(':id', $idUtilisateur, \PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

    /**
     * Détail d'une promotion.
     */
        public function getPromotion(int $idPromotion): array|false
    {
        $stmt = $this->db->prepare("
            SELECT 
                pr.Id_promotion,
                pr.Libelle,
                pr.Annee,
                pr.Filiere,
                pr.Id_pilote,
                p.nom AS pilote_nom,
                p.prenom AS pilote_prenom
            FROM PROMOTION pr
            JOIN PILOTE p ON p.Id_pilote = pr.Id_pilote
            WHERE pr.Id_promotion = :id
        ");
        $stmt->execute([':id' => $idPromotion]);
        return $stmt->fetch();
    }

    /**
     * Vérifie qu'une promotion appartient au pilote.
     */
    public function promotionAppartientAuPilote(int $idUtilisateur, int $idPromotion): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM PROMOTION pr
            JOIN PILOTE p ON p.Id_pilote = pr.Id_pilote
            WHERE p.Id_utilisateur = :id_util AND pr.Id_promotion = :id_promo
        ");
        $stmt->execute([':id_util' => $idUtilisateur, ':id_promo' => $idPromotion]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Étudiants d'une promotion avec stats candidatures.
     */
    public function getEtudiants(int $idPromotion, int $limit = 999, int $offset = 0): array
{
    $stmt = $this->db->prepare("
        SELECT
            et.Id_etudiant, et.nom, et.prenom, et.Telephone, et.Statut_recherche,
            u.Email,
            COUNT(p.Id_offre) AS nb_candidatures
        FROM ETUDIANT et
        JOIN APPARTIENT ap     ON ap.Id_etudiant   = et.Id_etudiant
        JOIN UTILISATEUR u     ON u.Id_utilisateur = et.Id_utilisateur
        LEFT JOIN POSTULE p    ON p.Id_etudiant    = et.Id_etudiant
        WHERE ap.Id_promotion = :id
        GROUP BY et.Id_etudiant
        ORDER BY et.nom, et.prenom
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':id', $idPromotion, \PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

public function countEtudiants(int $idPromotion): int
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM ETUDIANT et
        JOIN APPARTIENT ap ON ap.Id_etudiant = et.Id_etudiant
        WHERE ap.Id_promotion = :id
    ");
    $stmt->bindValue(':id', $idPromotion, \PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

    /**
     * Vérifie qu'un étudiant est dans une promotion du pilote.
     */
    public function etudiantDuPilote(int $idUtilisateur, int $idEtudiant): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM APPARTIENT ap
            JOIN PROMOTION pr ON pr.Id_promotion = ap.Id_promotion
            JOIN PILOTE p     ON p.Id_pilote      = pr.Id_pilote
            WHERE p.Id_utilisateur = :id_util AND ap.Id_etudiant = :id_et
        ");
        $stmt->execute([':id_util' => $idUtilisateur, ':id_et' => $idEtudiant]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Profil complet d'un étudiant.
     */
    public function getEtudiantProfil(int $idEtudiant): array|false
    {
        $stmt = $this->db->prepare("
            SELECT
                et.Id_etudiant, et.nom, et.prenom, et.Telephone, et.Statut_recherche,
                u.Email,
                GROUP_CONCAT(pr.Libelle SEPARATOR ', ') AS promotions
            FROM ETUDIANT et
            JOIN UTILISATEUR u      ON u.Id_utilisateur  = et.Id_utilisateur
            LEFT JOIN APPARTIENT ap ON ap.Id_etudiant    = et.Id_etudiant
            LEFT JOIN PROMOTION pr  ON pr.Id_promotion   = ap.Id_promotion
            WHERE et.Id_etudiant = :id
            GROUP BY et.Id_etudiant
        ");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetch();
    }

    /**
     * Candidatures d'un étudiant avec infos offre + entreprise + fichiers.
     */
    public function getCandidatures(int $idEtudiant): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.Id_offre, p.Date_candidature, p.Statut, p.Lettre_motivation,
                p.Id_cv,
                o.titre                   AS Titre,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.Id_entreprise,
                e.Nom                     AS Nom_entreprise,
                se.Ville,
                cv.Chemin_fichier         AS cv_chemin,
                cv.Nom_fichier            AS cv_nom
            FROM POSTULE p
            JOIN OFFRE o               ON o.Id_offre        = p.Id_offre
            JOIN ENTREPRISE e          ON e.Id_entreprise   = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site      = o.Id_site
            LEFT JOIN CV cv            ON cv.Id_cv          = p.Id_cv
            WHERE p.Id_etudiant = :id
            ORDER BY p.Date_candidature DESC
        ");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    /**
     * CVs d'un étudiant.
     */
    public function getCvs(int $idEtudiant): array
    {
        $stmt = $this->db->prepare("
            SELECT Id_cv, Nom_fichier, Date_depot, Cv_principal, Chemin_fichier
            FROM CV
            WHERE Id_etudiant = :id
            ORDER BY Cv_principal DESC, Date_depot DESC
        ");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    // ── CRUD admin ────────────────────────────────────────────────────────────

    public function findAll(int $limit = 20, int $offset = 0): array
{
    $stmt = $this->db->prepare("
        SELECT p.Id_pilote, p.nom, p.prenom, p.Telephone, u.Email,
               COUNT(pr.Id_promotion) AS nb_promotions
        FROM PILOTE p
        JOIN UTILISATEUR u     ON u.Id_utilisateur = p.Id_utilisateur
        LEFT JOIN PROMOTION pr ON pr.Id_pilote     = p.Id_pilote
        GROUP BY p.Id_pilote
        ORDER BY p.nom, p.prenom
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

        public function findByIdFull(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.Id_pilote,
                p.nom,
                p.prenom,
                p.Telephone,
                p.Id_utilisateur,
                u.Email
            FROM PILOTE p
            JOIN UTILISATEUR u ON u.Id_utilisateur = p.Id_utilisateur
            WHERE p.Id_pilote = :id
        ");
        $stmt->execute([':id' => $id]);
        $pilote = $stmt->fetch();

        if (!$pilote) return false;

        $stmt = $this->db->prepare(
            "SELECT Id_promotion, Libelle, Annee, Filiere 
             FROM PROMOTION 
             WHERE Id_pilote = :id"
        );
        $stmt->execute([':id' => $id]);
        $pilote['promotions'] = $stmt->fetchAll();

        return $pilote;
    }

    public function createPilote(array $data): int
    {
        // Créer l'utilisateur
        $stmt = $this->db->prepare("
            INSERT INTO UTILISATEUR (Email, Mot_de_passe, Role)
            VALUES (:email, :mdp, 'pilote')
        ");
        $stmt->execute([
            ':email' => $data['email'],
            ':mdp'   => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
        ]);
        $idUtilisateur = (int) $this->db->lastInsertId();

        // Créer le pilote
        $stmt = $this->db->prepare("
            INSERT INTO PILOTE (nom, prenom, Telephone, Id_utilisateur)
            VALUES (:nom, :prenom, :tel, :id_u)
        ");
        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':tel'    => $data['telephone'] ?? null,
            ':id_u'   => $idUtilisateur,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePilote(int $idPilote, array $data): void
    {
        // Mettre à jour PILOTE
        $stmt = $this->db->prepare("
            UPDATE PILOTE SET nom = :nom, prenom = :prenom, Telephone = :tel
            WHERE Id_pilote = :id
        ");
        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':tel'    => $data['telephone'] ?? null,
            ':id'     => $idPilote,
        ]);

        // Mettre à jour email dans UTILISATEUR
        $stmt = $this->db->prepare("
            UPDATE UTILISATEUR u
            JOIN PILOTE p ON p.Id_utilisateur = u.Id_utilisateur
            SET u.Email = :email
            WHERE p.Id_pilote = :id
        ");
        $stmt->execute([':email' => $data['email'], ':id' => $idPilote]);

        // Mot de passe si fourni
        if (!empty($data['mot_de_passe'])) {
            $stmt = $this->db->prepare("
                UPDATE UTILISATEUR u
                JOIN PILOTE p ON p.Id_utilisateur = u.Id_utilisateur
                SET u.Mot_de_passe = :mdp
                WHERE p.Id_pilote = :id
            ");
            $stmt->execute([
                ':mdp' => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
                ':id'  => $idPilote,
            ]);
        }
    }

    public function deletePilote(int $idPilote): void
    {
        // CASCADE sur UTILISATEUR supprime PILOTE automatiquement
        $stmt = $this->db->prepare("
            DELETE u FROM UTILISATEUR u
            JOIN PILOTE p ON p.Id_utilisateur = u.Id_utilisateur
            WHERE p.Id_pilote = :id
        ");
        $stmt->execute([':id' => $idPilote]);
    }

    public function createPromotion(array $data): int
{
    $stmt = $this->db->prepare("
        INSERT INTO PROMOTION (Libelle, Annee, Filiere, Id_pilote)
        VALUES (:libelle, :annee, :filiere, :pilote)
    ");
    $stmt->execute([
        ':libelle' => $data['libelle'],
        ':annee'   => $data['annee'],
        ':filiere' => $data['filiere'],
        ':pilote'  => $data['id_pilote'] ?: null,
    ]);
    return (int) $this->db->lastInsertId();
}

public function updatePromotion(int $id, array $data): void
{
    $stmt = $this->db->prepare("
        UPDATE PROMOTION SET Libelle = :libelle, Annee = :annee,
               Filiere = :filiere, Id_pilote = :pilote
        WHERE Id_promotion = :id
    ");
    $stmt->execute([
        ':libelle' => $data['libelle'],
        ':annee'   => $data['annee'],
        ':filiere' => $data['filiere'],
        ':pilote'  => $data['id_pilote'] ?: null,
        ':id'      => $id,
    ]);
}

public function deletePromotion(int $id): void
{
    $this->db->prepare("DELETE FROM PROMOTION WHERE Id_promotion = :id")
             ->execute([':id' => $id]);
}

public function getAllPromotions(int $limit = 20, int $offset = 0): array
{
    $stmt = $this->db->prepare("
        SELECT pr.Id_promotion, pr.Libelle, pr.Annee, pr.Filiere,
               p.nom AS pilote_nom, p.prenom AS pilote_prenom,
               COUNT(ap.Id_etudiant) AS nb_etudiants
        FROM PROMOTION pr
        LEFT JOIN PILOTE p      ON p.Id_pilote     = pr.Id_pilote
        LEFT JOIN APPARTIENT ap ON ap.Id_promotion = pr.Id_promotion
        GROUP BY pr.Id_promotion
        ORDER BY pr.Annee DESC, pr.Libelle
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

public function countAllPromotions(): int
{
    return (int) $this->db->query("SELECT COUNT(*) FROM PROMOTION")->fetchColumn();
}

}
