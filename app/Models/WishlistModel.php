<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;

class WishlistModel extends BaseModel
{
    /**
     * Récupère toutes les offres en wishlist d'un étudiant
     * (via table AJOUTE — nouveau schéma corrigé par l'enseignante).
     */
    public function findByEtudiant(int $idEtudiant): array
    {
        $sql = "
            SELECT
                o.Id_offre,
                o.titre                   AS Titre,
                o.description             AS Description,
                o.gratification_par_heure AS Base_remuneration,
                o.date_creation_offre     AS Date_offre,
                a.Date_ajout,
                e.Nom   AS Nom_entreprise,
                e.Id_entreprise
            FROM AJOUTE a
            JOIN OFFRE      o ON o.Id_offre       = a.Id_offre
            JOIN ENTREPRISE e ON e.Id_entreprise  = o.Id_entreprise
            JOIN ETUDIANT   et ON et.Id_etudiant  = a.Id_etudiant
            WHERE et.Id_utilisateur = :id_utilisateur
            ORDER BY a.Date_ajout DESC
        ";
        return $this->fetchAll($sql, [':id_utilisateur' => $idEtudiant]);
    }

    /**
     * Ajoute une offre à la wishlist (ignore les doublons).
     * $idUtilisateur = $_SESSION['user_id']
     */
    public function add(int $idUtilisateur, int $idOffre): void
    {
        $idEtudiant = $this->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) return;

        $sql = "
            INSERT IGNORE INTO AJOUTE (Id_offre, Id_etudiant, Date_ajout)
            VALUES (:offre, :etudiant, CURRENT_DATE)
        ";
        $this->execute($sql, [':offre' => $idOffre, ':etudiant' => $idEtudiant]);
    }

    /**
     * Retire une offre de la wishlist.
     */
    public function remove(int $idUtilisateur, int $idOffre): void
    {
        $idEtudiant = $this->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) return;

        $sql = "DELETE FROM AJOUTE WHERE Id_offre = :offre AND Id_etudiant = :etudiant";
        $this->execute($sql, [':offre' => $idOffre, ':etudiant' => $idEtudiant]);
    }

    /**
     * Vérifie si une offre est déjà en wishlist.
     */
    public function exists(int $idUtilisateur, int $idOffre): bool
    {
        $idEtudiant = $this->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) return false;

        $sql = "
            SELECT COUNT(*) FROM AJOUTE
            WHERE Id_offre = :offre AND Id_etudiant = :etudiant
        ";
        return (int) $this->fetchColumn($sql, [':offre' => $idOffre, ':etudiant' => $idEtudiant]) > 0;
    }

    /**
     * Récupère l'Id_etudiant depuis l'Id_utilisateur de session.
     */
    private function getIdEtudiant(int $idUtilisateur): int|false
    {
        $sql  = "SELECT Id_etudiant FROM ETUDIANT WHERE Id_utilisateur = :id";
        $result = $this->fetchColumn($sql, [':id' => $idUtilisateur]);
        return $result !== false ? (int) $result : false;
    }
}
