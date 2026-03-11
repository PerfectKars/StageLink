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

    public function mentionsLegales(): void
    {
        $this->render('home/mentions-legales', [
            'title' => 'Mentions légales',
        ]);
    }
}
