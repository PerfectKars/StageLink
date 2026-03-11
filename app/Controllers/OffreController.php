<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\OffreModel;
use App\Models\EntrepriseModel;

class OffreController extends BaseController
{
    private OffreModel $offreModel;
    private EntrepriseModel $entrepriseModel;

    private const PER_PAGE = 10;

    public function __construct()
    {
        $this->offreModel      = new OffreModel();
        $this->entrepriseModel = new EntrepriseModel();
    }

    /**
     * Liste des offres avec recherche et pagination (SFx 7)
     */
    public function index(): void
    {
        $filters = [
            'titre'      => $_GET['titre']      ?? '',
            'ville'      => $_GET['ville']      ?? '',
            'competence' => $_GET['competence'] ?? '',
        ];

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offres = $this->offreModel->search($filters, $page, self::PER_PAGE);
        $total  = $this->offreModel->count();

        $this->render('offre/index', [
            'title'    => 'Offres de stage',
            'offres'   => $offres,
            'filters'  => $filters,
            'page'     => $page,
            'perPage'  => self::PER_PAGE,
            'total'    => $total,
        ]);
    }

    /**
     * Détail d'une offre (SFx 7)
     */
    public function show(string $id): void
    {
        $offre = $this->offreModel->findByIdFull((int) $id);

        if (!$offre) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Offre introuvable']);
            return;
        }

        $this->render('offre/show', [
            'title' => $offre['titre'],
            'offre' => $offre,
        ]);
    }

    /**
     * Formulaire de création (SFx 8) — Pilote/Admin
     */
    public function createForm(): void
    {
        $this->requireRole('admin', 'pilote');

        $entreprises = $this->entrepriseModel->findAll(100);

        $this->render('offre/form', [
            'title'       => 'Créer une offre',
            'offre'       => null,
            'entreprises' => $entreprises,
        ]);
    }

    /**
     * Traitement création (SFx 8)
     */
    public function create(): void
    {
        $this->requireRole('admin', 'pilote');

        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('offre/form', [
                'title'       => 'Créer une offre',
                'offre'       => $data,
                'errors'      => $errors,
                'entreprises' => $this->entrepriseModel->findAll(100),
            ]);
            return;
        }

        $competences = $_POST['competences'] ?? [];
        $id = $this->offreModel->create($data, $competences);

        $this->redirect('/offres/' . $id);
    }

    /**
     * Formulaire d'édition (SFx 9)
     */
    public function editForm(string $id): void
    {
        $this->requireRole('admin', 'pilote');

        $offre = $this->offreModel->findByIdFull((int) $id);

        if (!$offre) {
            $this->redirect('/offres');
        }

        $this->render('offre/form', [
            'title'       => 'Modifier l\'offre',
            'offre'       => $offre,
            'entreprises' => $this->entrepriseModel->findAll(100),
        ]);
    }

    /**
     * Traitement édition (SFx 9)
     */
    public function edit(string $id): void
    {
        $this->requireRole('admin', 'pilote');

        $data        = $this->getFormData();
        $competences = $_POST['competences'] ?? [];
        $this->offreModel->update((int) $id, $data, $competences);

        $this->redirect('/offres/' . $id);
    }

    /**
     * Suppression (SFx 10)
     */
    public function delete(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $this->offreModel->deleteById((int) $id);
        $this->redirect('/offres');
    }

    /**
     * Statistiques (SFx 11)
     */
    public function statistiques(): void
    {
        $this->requireRole('admin', 'pilote');

        $stats = $this->offreModel->getStatistiques();

        $this->render('offre/statistiques', [
            'title' => 'Statistiques des offres',
            'stats' => $stats,
        ]);
    }

    private function getFormData(): array
    {
        return [
            'titre'              => trim($_POST['titre'] ?? ''),
            'description_mission'=> trim($_POST['description_mission'] ?? ''),
            'date_debut'         => $_POST['date_debut'] ?? null,
            'duree_mois'         => (int) ($_POST['duree_mois'] ?? 0),
            'gratification'      => (float) ($_POST['gratification'] ?? 0),
            'ville_stage'        => trim($_POST['ville_stage'] ?? ''),
            'teletravail'        => isset($_POST['teletravail']) ? 1 : 0,
            'id_entreprise'      => (int) ($_POST['id_entreprise'] ?? 0),
            'id_type'            => (int) ($_POST['id_type'] ?? 0),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['titre'])) {
            $errors['titre'] = 'Le titre est obligatoire.';
        }

        if (empty($data['description_mission'])) {
            $errors['description_mission'] = 'La description est obligatoire.';
        }

        if ($data['id_entreprise'] === 0) {
            $errors['id_entreprise'] = 'Veuillez sélectionner une entreprise.';
        }

        return $errors;
    }
}
