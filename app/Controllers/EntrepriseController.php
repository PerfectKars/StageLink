<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EntrepriseModel;

class EntrepriseController extends BaseController
{
    private EntrepriseModel $model;
    private const PER_PAGE = 10;

    public function __construct()
    {
        $this->model = new EntrepriseModel();
    }

    public function index(): void
    {
        $filters = [
            'nom'     => $_GET['nom']     ?? '',
            'secteur' => $_GET['secteur'] ?? '',
        ];
        $page        = max(1, (int) ($_GET['page'] ?? 1));
        $entreprises = $this->model->search($filters, $page, self::PER_PAGE);
        $total       = $this->model->count();

        $this->render('entreprise/index', [
            'title'       => 'Entreprises',
            'entreprises' => $entreprises,
            'filters'     => $filters,
            'page'        => $page,
            'perPage'     => self::PER_PAGE,
            'total'       => $total,
        ]);
    }

    public function show(string $id): void
    {
        $entreprise = $this->model->findByIdFull((int) $id);
        if (!$entreprise) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Entreprise introuvable']);
            return;
        }
        $this->render('entreprise/show', [
            'title'      => $entreprise['Nom'],
            'entreprise' => $entreprise,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole('admin', 'pilote');
        $this->render('entreprise/form', ['title' => 'Créer une entreprise', 'entreprise' => null]);
    }

    public function create(): void
    {
        $this->requireRole('admin', 'pilote');
        $data = $this->getFormData();
        $id   = $this->model->create($data);
        $this->redirect('/entreprises/' . $id);
    }

    public function editForm(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $entreprise = $this->model->findByIdFull((int) $id);
        $this->render('entreprise/form', ['title' => 'Modifier entreprise', 'entreprise' => $entreprise]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $this->model->update((int) $id, $this->getFormData());
        $this->redirect('/entreprises/' . $id);
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $this->model->deleteById((int) $id);
        $this->redirect('/entreprises');
    }

    public function noter(string $id): void
    {
        $this->requireRole('etudiant');
        $this->model->noter(
            (int) $id,
            (int) $_SESSION['user']['id'],
            (int) $_POST['note'],
            $_POST['commentaire'] ?? ''
        );
        $this->redirect('/entreprises/' . $id);
    }

    private function getFormData(): array
    {
        return [
            'Nom'           => trim($_POST['Nom'] ?? ''),
            'Description'   => trim($_POST['Description'] ?? ''),
            'Email_contact' => trim($_POST['Email_contact'] ?? ''),
            'Tel_contact'   => trim($_POST['Tel_contact'] ?? ''),
        ];
    }
}
