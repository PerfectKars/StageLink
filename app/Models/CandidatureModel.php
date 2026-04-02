<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;

class CandidatureModel extends BaseModel
{
    protected string $table      = 'POSTULE';
    protected string $primaryKey = 'Id_offre';

    /** Candidatures de l'étudiant connecté (via Id_utilisateur session) */
    public function getByEtudiant(int $idUtilisateur, int $limit = 999, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.Id_offre, p.Id_cv AS cv_id, p.Date_candidature, p.Statut, p.Lettre_motivation,
                o.titre                   AS Titre,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.Id_entreprise,
                e.Nom                     AS Nom_entreprise,
                se.Ville,
                cv.Chemin_fichier         AS cv_chemin,
                cv.Nom_fichier            AS cv_nom
            FROM POSTULE p
            JOIN ETUDIANT et             ON et.Id_etudiant   = p.Id_etudiant
            JOIN OFFRE    o              ON o.Id_offre        = p.Id_offre
            JOIN ENTREPRISE e            ON e.Id_entreprise  = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site       = o.Id_site
            LEFT JOIN CV cv              ON cv.Id_cv         = p.Id_cv
            WHERE et.Id_utilisateur = :id
            ORDER BY p.Date_candidature DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':id', $idUtilisateur, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    

    /**
     * Candidatures de tous les étudiants des promotions du pilote.
     * Retourne ['candidatures' => [...], 'promotions' => [...]]
     */
    public function getByPromotion(int $idUtilisateur): array
    {
        // Récupérer Id_pilote depuis Id_utilisateur
        $stmt = $this->db->prepare(
            "SELECT Id_pilote FROM PILOTE WHERE Id_utilisateur = :id"
        );
        $stmt->execute([':id' => $idUtilisateur]);
        $idPilote = $stmt->fetchColumn();

        if (!$idPilote) return ['candidatures' => [], 'promotions' => []];

        // Promotions du pilote
        $stmt = $this->db->prepare(
            "SELECT Id_promotion, Libelle, Annee, Filiere FROM PROMOTION WHERE Id_pilote = :id"
        );
        $stmt->execute([':id' => $idPilote]);
        $promotions = $stmt->fetchAll();

        if (empty($promotions)) return ['candidatures' => [], 'promotions' => []];

        $idPromos = array_column($promotions, 'Id_promotion');
        $inClause = implode(',', array_fill(0, count($idPromos), '?'));

        // Toutes les candidatures des étudiants de ces promotions
        $sql = "
            SELECT
                p.Id_offre, p.Id_etudiant, p.Date_candidature, p.Statut,
                p.Lettre_motivation, p.Id_cv,
                et.nom                    AS etudiant_nom,
                et.prenom                 AS etudiant_prenom,
                pr.Libelle                AS promotion,
                o.titre                   AS Titre,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.Id_entreprise,
                e.Nom                     AS Nom_entreprise,
                se.Ville,
                cv.Chemin_fichier         AS cv_chemin,
                cv.Nom_fichier            AS cv_nom
            FROM POSTULE p
            JOIN ETUDIANT et           ON et.Id_etudiant    = p.Id_etudiant
            JOIN APPARTIENT ap         ON ap.Id_etudiant    = et.Id_etudiant
            JOIN PROMOTION pr          ON pr.Id_promotion   = ap.Id_promotion
            JOIN OFFRE o               ON o.Id_offre         = p.Id_offre
            JOIN ENTREPRISE e          ON e.Id_entreprise    = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site       = o.Id_site
            LEFT JOIN CV cv            ON cv.Id_cv           = p.Id_cv
            WHERE ap.Id_promotion IN ($inClause)
            ORDER BY p.Date_candidature DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($idPromos);

        return [
            'candidatures' => $stmt->fetchAll(),
            'promotions'   => $promotions,
        ];
    }

    /**
     * Vérifie qu'un étudiant appartient à une promotion du pilote.
     */
    public function etudiantDansPromotion(int $idUtilisateur, int $idEtudiant): bool
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
     * Candidatures d'un étudiant par son Id_etudiant (pour vue pilote).
     */
    public function getByEtudiantId(int $idEtudiant): array
    {
        $stmt = $this->db->prepare("
    SELECT
        p.Id_offre, p.Id_cv AS cv_id, p.Date_candidature, p.Statut, p.Lettre_motivation,
        o.titre                   AS Titre,
        o.gratification_par_heure AS Base_remuneration,
        o.duree_mois,
        o.Id_entreprise,
        e.Nom                     AS Nom_entreprise,
        se.Ville,
        cv.Chemin_fichier         AS cv_chemin,
        cv.Nom_fichier            AS cv_nom
    FROM POSTULE p
    JOIN ETUDIANT et            ON et.Id_etudiant   = p.Id_etudiant
    JOIN OFFRE    o             ON o.Id_offre        = p.Id_offre
    JOIN ENTREPRISE e           ON e.Id_entreprise  = o.Id_entreprise
    LEFT JOIN SITE_ENTREPRISE se ON se.Id_site       = o.Id_site
    LEFT JOIN CV cv             ON cv.Id_cv          = p.Id_cv
    WHERE et.Id_utilisateur = :id
    ORDER BY p.Date_candidature DESC
");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    /**
     * Infos d'un étudiant par Id_etudiant.
     */
    public function getEtudiantInfo(int $idEtudiant): array|false
    {
        $stmt = $this->db->prepare("
            SELECT et.Id_etudiant, et.nom, et.prenom, et.Telephone,
                   u.Email, pr.Libelle AS promotion
            FROM ETUDIANT et
            JOIN UTILISATEUR u     ON u.Id_utilisateur  = et.Id_utilisateur
            LEFT JOIN APPARTIENT ap ON ap.Id_etudiant   = et.Id_etudiant
            LEFT JOIN PROMOTION pr  ON pr.Id_promotion  = ap.Id_promotion
            WHERE et.Id_etudiant = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetch();
    }

    /** Postule à une offre avec CV, LM PDF et autres documents. */
    public function postuler(
        int $idUtilisateur, int $idOffre, string $lettre,
        ?int $idCv, ?string $cheminLm, array $autresChemins = []
    ): bool {
        $idEtudiant = $this->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) return false;

        $stmt = $this->db->prepare("
            INSERT INTO POSTULE (Id_etudiant, Id_offre, Lettre_motivation, Date_candidature, Statut, Id_cv)
            VALUES (:etudiant, :offre, :lettre, CURDATE(), 'En attente', :idcv)
        ");
        $ok = $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre'    => $idOffre,
            ':lettre'   => $lettre ?: null,
            ':idcv'     => $idCv,
        ]);

        if ($ok) {
            $this->db->prepare(
                "UPDATE OFFRE SET nb_candidatures = nb_candidatures + 1 WHERE Id_offre = :id"
            )->execute([':id' => $idOffre]);
        }

        return $ok;
    }

    /** Vérifie si déjà postulé */
    public function aDejaPostule(int $idUtilisateur, int $idOffre): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM POSTULE p
            JOIN ETUDIANT e ON e.Id_etudiant = p.Id_etudiant
            WHERE e.Id_utilisateur = :id AND p.Id_offre = :offre
        ");
        $stmt->execute([':id' => $idUtilisateur, ':offre' => $idOffre]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Id_etudiant depuis Id_utilisateur */
    public function getIdEtudiant(int $idUtilisateur): int|false
    {
        $stmt = $this->db->prepare("SELECT Id_etudiant FROM ETUDIANT WHERE Id_utilisateur = :id");
        $stmt->execute([':id' => $idUtilisateur]);
        $r = $stmt->fetchColumn();
        return $r !== false ? (int) $r : false;
    }

    /** Sauvegarde un CV en BDD */
    public function saveCv(int $idEtudiant, string $nomFichier, string $chemin, bool $principal = false): int
    {
        if ($principal) {
            $this->db->prepare("UPDATE CV SET Cv_principal = 0 WHERE Id_etudiant = :id")
                     ->execute([':id' => $idEtudiant]);
        }
        $stmt = $this->db->prepare("
            INSERT INTO CV (Nom_fichier, Chemin_fichier, Date_depot, Cv_principal, Id_etudiant)
            VALUES (:nom, :chemin, CURDATE(), :principal, :etudiant)
        ");
        $stmt->execute([
            ':nom'       => $nomFichier,
            ':chemin'    => $chemin,
            ':principal' => $principal ? 1 : 0,
            ':etudiant'  => $idEtudiant,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** CV existants d'un étudiant */
    public function getCvEtudiant(int $idUtilisateur): array
    {
        $stmt = $this->db->prepare("
            SELECT cv.Id_cv, cv.Nom_fichier, cv.Date_depot, cv.Cv_principal
            FROM CV cv
            JOIN ETUDIANT e ON e.Id_etudiant = cv.Id_etudiant
            WHERE e.Id_utilisateur = :id
            ORDER BY cv.Cv_principal DESC, cv.Date_depot DESC
        ");
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    /** Récupère chemin CV d'une candidature spécifique */
public function getCvCandidature(int $idOffre, int $idEtudiant): array|false
{
    $stmt = $this->db->prepare("
        SELECT cv.Chemin_fichier AS cv_chemin, cv.Nom_fichier AS cv_nom
        FROM POSTULE p
        LEFT JOIN CV cv ON cv.Id_cv = p.Id_cv
        WHERE p.Id_offre = :offre AND p.Id_etudiant = :etudiant
    ");
    $stmt->execute([':offre' => $idOffre, ':etudiant' => $idEtudiant]);
    return $stmt->fetch();
}

public function updateStatut(int $idOffre, int $idEtudiant, string $statut): void
{
    $this->db->prepare("
        UPDATE POSTULE SET Statut = :statut
        WHERE Id_offre = :offre AND Id_etudiant = :etudiant
    ")->execute([
        ':statut'   => $statut,
        ':offre'    => $idOffre,
        ':etudiant' => $idEtudiant,
    ]);
}

public function getCvById(int $idCv): array|false
{
    $stmt = $this->db->prepare(
        "SELECT Id_cv, Nom_fichier, Chemin_fichier, Id_etudiant FROM CV WHERE Id_cv = :id"
    );
    $stmt->execute([':id' => $idCv]);
    return $stmt->fetch();
}

public function confirmerStage(int $idUtilisateur, int $idOffre): bool
{
    $idEtudiant = $this->getIdEtudiant($idUtilisateur);
    if (!$idEtudiant) return false;

    // Confirmer cette candidature
    $this->db->prepare("
        UPDATE POSTULE SET Statut = 'Confirmé'
        WHERE Id_offre = :offre AND Id_etudiant = :etudiant
    ")->execute([':offre' => $idOffre, ':etudiant' => $idEtudiant]);

    // Refuser toutes les autres candidatures de l'étudiant
    $this->db->prepare("
        UPDATE POSTULE SET Statut = 'Refusé'
        WHERE Id_etudiant = :etudiant
        AND Id_offre != :offre
        AND Statut NOT IN ('Refusé', 'Confirmé')
    ")->execute([':etudiant' => $idEtudiant, ':offre' => $idOffre]);

    // Mettre Statut_recherche à "Stage trouvé"
    $this->db->prepare("
        UPDATE ETUDIANT SET Statut_recherche = 'Stage trouvé'
        WHERE Id_etudiant = :etudiant
    ")->execute([':etudiant' => $idEtudiant]);

    return true;
}

public function getStatutCandidature(int $idUtilisateur, int $idOffre): string|null
{
    $stmt = $this->db->prepare("
        SELECT p.Statut FROM POSTULE p
        JOIN ETUDIANT e ON e.Id_etudiant = p.Id_etudiant
        WHERE e.Id_utilisateur = :id AND p.Id_offre = :offre
    ");
    $stmt->execute([':id' => $idUtilisateur, ':offre' => $idOffre]);
    $result = $stmt->fetchColumn();
    return $result ?: null;
}

public function countByEtudiant(int $idUtilisateur): int
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM POSTULE p
        JOIN ETUDIANT et ON et.Id_etudiant = p.Id_etudiant
        WHERE et.Id_utilisateur = :id
    ");
    $stmt->bindValue(':id', $idUtilisateur, \PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

}
