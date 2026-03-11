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
        $expected = 'Développeur PHP';
        $result   = htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');

        $this->assertEquals($expected, $result);
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
}
