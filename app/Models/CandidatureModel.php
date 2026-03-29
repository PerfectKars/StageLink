<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;

class CandidatureModel extends BaseModel
{
    protected string $table      = 'POSTULE';
    protected string $primaryKey = 'Id_offre';

    /**
     * Récupère toutes les candidatures d'un étudiant.
     */
    public function getByEtudiant(int $idUtilisateur): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.Id_offre,
                p.Date_candidature,
                p.Statut,
                p.Lettre_motivation,
                o.titre                   AS Titre,
                o.gratification_par_heure AS Base_remuneration,
                o.duree_mois,
                o.Id_entreprise,
                e.Nom                     AS Nom_entreprise,
                se.Ville
            FROM POSTULE p
            JOIN ETUDIANT et          ON et.Id_etudiant    = p.Id_etudiant
            JOIN OFFRE    o           ON o.Id_offre         = p.Id_offre
            JOIN ENTREPRISE e         ON e.Id_entreprise    = o.Id_entreprise
            LEFT JOIN SITE_ENTREPRISE se ON se.Id_site      = o.Id_site
            WHERE et.Id_utilisateur = :id
            ORDER BY p.Date_candidature DESC
        ");
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    /**
     * Postule à une offre avec CV, LM PDF et autres documents.
     */
    public function postuler(
        int $idUtilisateur,
        int $idOffre,
        string $lettre,
        ?int $idCv,
        ?string $cheminLm,
        array $autresChemins = []
    ): bool {
        $idEtudiant = $this->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) return false;

        // Sérialiser les chemins des documents annexes en JSON
        $docsJson = !empty($autresChemins) ? json_encode($autresChemins) : null;

        $stmt = $this->db->prepare("
            INSERT INTO POSTULE
                (Id_etudiant, Id_offre, Lettre_motivation, Date_candidature, Statut, Id_cv)
            VALUES
                (:etudiant, :offre, :lettre, CURDATE(), 'En attente', :idcv)
        ");
        $ok = $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre'    => $idOffre,
            ':lettre'   => $lettre ?: null,
            ':idcv'     => $idCv,
        ]);

        if ($ok) {
            // Incrémenter le compteur de candidatures
            $this->db->prepare(
                "UPDATE OFFRE SET nb_candidatures = nb_candidatures + 1 WHERE Id_offre = :id"
            )->execute([':id' => $idOffre]);
        }

        return $ok;
    }

    /**
     * Vérifie si un étudiant a déjà postulé à une offre.
     */
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

    /**
     * Récupère l'Id_etudiant depuis l'Id_utilisateur.
     */
    public function getIdEtudiant(int $idUtilisateur): int|false
    {
        $stmt = $this->db->prepare(
            "SELECT Id_etudiant FROM ETUDIANT WHERE Id_utilisateur = :id"
        );
        $stmt->execute([':id' => $idUtilisateur]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int) $result : false;
    }

    /**
     * Sauvegarde un CV en BDD et retourne son Id_cv.
     */
    public function saveCv(int $idEtudiant, string $nomFichier, string $chemin, bool $principal = false): int
    {
        // Si principal, retire le flag des autres CV
        if ($principal) {
            $this->db->prepare(
                "UPDATE CV SET Cv_principal = 0 WHERE Id_etudiant = :id"
            )->execute([':id' => $idEtudiant]);
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

    /**
     * Récupère les CV existants d'un étudiant.
     */
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
}
