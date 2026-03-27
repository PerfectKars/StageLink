<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\BaseModel;

class CandidatureModel extends BaseModel
{
    protected string $table = 'POSTULE';
    protected string $primaryKey = 'Id_postule';

    public function getByEtudiant(int $idEtudiant): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, o.Titre, o.Base_remuneration, e.Nom AS entreprise
            FROM POSTULE p
            JOIN OFFRE o ON p.Id_offre = o.Id_offre
            JOIN ENTREPRISE e ON o.Id_entreprise = e.Id_entreprise
            WHERE p.Id_etudiant = :id
            ORDER BY p.Date_candidature DESC
        ");
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    public function postuler(int $idEtudiant, int $idOffre, string $lettre): bool
    {
        // Vérifier si déjà postulé
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM POSTULE
            WHERE Id_etudiant = :etudiant AND Id_offre = :offre
        ");
        $stmt->execute([':etudiant' => $idEtudiant, ':offre' => $idOffre]);
        if ($stmt->fetchColumn() > 0) return false;

        $stmt = $this->db->prepare("
            INSERT INTO POSTULE (Id_etudiant, Id_offre, Lettre_motivation, Date_candidature, Statut)
            VALUES (:etudiant, :offre, :lettre, CURDATE(), 'En attente')
        ");
        return $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre'    => $idOffre,
            ':lettre'   => $lettre,
        ]);
    }
}
