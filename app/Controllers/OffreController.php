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
            'title'   => 'Offres de stage',
            'offres'  => $offres,
            'filters' => $filters,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
            'total'   => $total,
        ]);
    }

    public function show(string $id): void
    {
        $offre = $this->offreModel->findByIdFull((int) $id);
        if (!$offre) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Offre introuvable']);
            return;
        }
        $this->render('offre/show', [
            'title' => $offre['Titre'],
            'offre' => $offre,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole('admin', 'pilote');
        $this->render('offre/form', [
            'title'              => 'Créer une offre',
            'offre'              => null,
            'entreprises'        => $this->entrepriseModel->findAll(100),
            'sitesParEntreprise' => $this->entrepriseModel->getAllSitesGrouped(),
            'competences'        => $this->offreModel->findAllCompetences(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin', 'pilote');
        $this->verifyCsrf();
        $data        = $this->getFormData();
        $errors      = $this->validate($data);
        $competences = $_POST['competences'] ?? [];

        if (!empty($errors)) {
            $this->render('offre/form', [
                'title'              => 'Créer une offre',
                'offre'              => $data,
                'errors'             => $errors,
                'entreprises'        => $this->entrepriseModel->findAll(100),
                'sitesParEntreprise' => $this->entrepriseModel->getAllSitesGrouped(),
                'competences'        => $this->offreModel->findAllCompetences(),
            ]);
            return;
        }

        $id = $this->offreModel->create($data, $competences);
        $this->redirect('/offres/' . $id);
    }

    public function editForm(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $offre = $this->offreModel->findByIdFull((int) $id);
        $this->render('offre/form', [
            'title'              => 'Modifier l\'offre',
            'offre'              => $offre,
            'entreprises'        => $this->entrepriseModel->findAll(100),
            'sitesParEntreprise' => $this->entrepriseModel->getAllSitesGrouped(),
            'competences'        => $this->offreModel->findAllCompetences(),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $this->verifyCsrf();
        $data        = $this->getFormData();
        $competences = $_POST['competences'] ?? [];
        $this->offreModel->update((int) $id, $data, $competences);
        $this->redirect('/offres/' . $id);
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $this->verifyCsrf();
        $this->offreModel->deleteById((int) $id);
        $this->redirect('/offres');
    }

    public function statistiques(): void
    {
        $this->requireRole('admin', 'pilote');
        $stats = $this->offreModel->getStatistiques();
        $this->render('offre/statistiques', [
            'title' => 'Statistiques des offres',
            'stats' => $stats,
        ]);
    }

    protected function getFormData(): array
    {
        return [
            'Titre'             => trim($_POST['Titre']             ?? ''),
            'Description'       => trim($_POST['Description']       ?? ''),
            'Base_remuneration' => (float)($_POST['Base_remuneration'] ?? 0),
            'duree_mois'        => (int)($_POST['duree_mois']       ?? 0),
            'Date_offre'        => $_POST['Date_offre']             ?? date('Y-m-d'),
            'Id_entreprise'     => (int)($_POST['Id_entreprise']    ?? 0),
            'Id_site'           => (int)($_POST['Id_site']          ?? 0),
        ];
    }

    protected function validate(array $data): array
{
    $errors = [];
    if (empty($data['Titre']))
        $errors[] = 'Le titre est obligatoire.';
    if (empty($data['Description']))
        $errors[] = 'La description est obligatoire.';
    if ($data['Id_entreprise'] === 0)
        $errors[] = 'Veuillez sélectionner une entreprise.';
    if ($data['Id_site'] === 0)
        $errors[] = 'Veuillez sélectionner un lieu d\'exercice.';
    if ($data['duree_mois'] <= 0)
        $errors[] = 'La durée est obligatoire.';
    elseif ($data['duree_mois'] > 6)
        $errors[] = 'La durée maximale d\'un stage est de 6 mois.';
    if ($data['duree_mois'] > 2 && $data['Base_remuneration'] < 4.50)
        $errors[] = 'Pour un stage de plus de 2 mois, la gratification minimale légale est de 4,50 €/h.';
    return $errors;
}
}
