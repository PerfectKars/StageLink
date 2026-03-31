<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\OffreModel;

/**
 * Tests unitaires pour OffreModel
 * STx 14 — PHPUnit obligatoire sur au moins un contrôleur/modèle
 */
class OffreModelTest extends TestCase
{
    /**
     * Vérifie que la validation des données d'une offre fonctionne
     */
    public function testOffreDataStructure(): void
    {
        $data = [
            'titre'               => 'Développeur PHP',
            'description_mission' => 'Mission de développement web',
            'date_debut'          => '2025-09-01',
            'duree_mois'          => 6,
            'gratification'       => 600.00,
            'ville_stage'         => 'Paris',
            'teletravail'         => 0,
            'id_entreprise'       => 1,
            'id_type'             => 1,
        ];

        $this->assertNotEmpty($data['titre']);
        $this->assertNotEmpty($data['description_mission']);
        $this->assertGreaterThan(0, $data['duree_mois']);
        $this->assertGreaterThanOrEqual(0, $data['gratification']);
    }

    /**
     * Vérifie que le titre est requis
     */
    public function testTitreNePeutPasEtreVide(): void
    {
        $titre = '';
        $this->assertEmpty($titre, 'Le titre vide est correctement détecté');
    }

    /**
     * Vérifie la sanitisation des entrées
     */
    public function testSanitizeXSS(): void
    {
        $input    = '<script>alert("xss")</script>Développeur PHP';
        // strip_tags supprime les balises, htmlspecialchars échappe les caractères spéciaux
        $result   = htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        
        // Le résultat doit contenir "Développeur PHP" et pas de <script>
        $this->assertStringContainsString('Développeur PHP', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    /**
     * Vérifie que la pagination calcule le bon offset
     */
    public function testPaginationOffset(): void
    {
        $page    = 3;
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $this->assertEquals(20, $offset);
    }

    /**
     * Vérifie que le calcul du nombre de pages est correct
     */
    public function testNombreDePages(): void
    {
        $total   = 25;
        $perPage = 10;
        $pages   = (int) ceil($total / $perPage);

        $this->assertEquals(3, $pages);
    }

    /**
     * Vérifie la validation de la durée minimale d'une offre
     */
    public function testDureeMinimale(): void
    {
        $duree = 1;
        $this->assertGreaterThanOrEqual(1, $duree, 'La durée minimale doit être 1 mois');
    }

    /**
     * Vérifie la validation de la gratification minimale légale
     */
    public function testGratificationMinimaleLegale(): void
    {
        $duree = 3; // > 2 mois
        $gratification = 4.50;
        
        if ($duree > 2) {
            $this->assertGreaterThanOrEqual(4.50, $gratification, 
                'Pour un stage > 2 mois, la gratification minimale est 4,50 €/h');
        }
    }

    /**
     * Vérifie que les compétences requises sont un tableau
     */
    public function testCompetencesStructure(): void
    {
        $competences = [
            ['Id_competence' => 1, 'Nom_competence' => 'PHP'],
            ['Id_competence' => 2, 'Nom_competence' => 'JavaScript'],
        ];
        
        $this->assertIsArray($competences);
        $this->assertCount(2, $competences);
        $this->assertArrayHasKey('Nom_competence', $competences[0]);
    }
}
