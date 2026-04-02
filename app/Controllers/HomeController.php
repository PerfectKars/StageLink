<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\OffreModel;
use App\Models\EntrepriseModel;

class HomeController extends BaseController
{
    public function index(): void
    {
        $offreModel      = new OffreModel();
        $entrepriseModel = new EntrepriseModel();

        $this->render('home/index', [
            'title'            => 'StageLink — Trouvez votre stage',
            'dernieresOffres'  => $offreModel->search([], 1, 6),
            'nbOffres'         => $offreModel->count(),
            'nbEntreprises'    => $entrepriseModel->count(),
        ]);
    }

        /**
     * GET /mentions-legales
     */
    public function mentionsLegales(): void
    {
        $this->render('home/mentions-legales', [
            'title' => 'Mentions Légales'
        ]);
    }

    /**
     * GET /politique-de-confidentialite
     */
    public function politiqueConfidentialite(): void
    {
        $this->render('home/politique-confidentialite', [
            'title' => 'Politique de Confidentialité'
        ]);
    }

    /**
     * GET /politique-de-cookies
     */
    public function politiqueCookies(): void
    {
        $this->render('home/politique-cookies', [
            'title' => 'Politique des Cookies'
        ]);
    }

    /**
     * GET /droits-auteur
     */
    public function droitsAuteur(): void
    {
        $this->render('home/droits-auteur', [
            'title' => 'Droits d\'auteur'
        ]);
    }
}
