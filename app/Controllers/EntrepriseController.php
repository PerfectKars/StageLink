<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EntrepriseModel;

class EntrepriseController extends BaseController
{
    private EntrepriseModel $model;
    private const PER_PAGE = 9;

    public function __construct()
    {
        $this->model = new EntrepriseModel();
    }

    public function index(): void
    {
        $filters = [
            'nom'   => $_GET['nom']   ?? '',
            'ville' => $_GET['ville'] ?? '',
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
        $this->render('entreprise/form', [
            'title'      => 'Ajouter une entreprise',
            'entreprise' => null,
            'errors'     => [],
        ]);
    }

    public function create(): void
{
    $this->requireRole('admin', 'pilote');
    $this->verifyCsrf();

    $data   = $this->getFormData();
    $errors = $this->validate($data);

    if (!empty($errors)) {
        $this->render('entreprise/form', [
            'title'      => 'Ajouter une entreprise',
            'entreprise' => $data,
            'errors'     => $errors,
        ]);
        return;
    }

    try {
        $id = $this->model->create($data);
        // Sites supplémentaires
foreach ($_POST['sites'] ?? [] as $site) {
    if (!empty($site['Adresse'])) {
        $this->model->addSite($id, $site);
    }
}
        $_SESSION['flash_success'] = 'Entreprise créée avec succès.';
        $this->redirect('/entreprises/' . $id);
    } catch (\PDOException $e) {
        if (str_contains($e->getMessage(), '1062')) {
            $errors[] = 'Ce numéro SIRET est déjà enregistré pour une autre entreprise.';
        } else {
            $errors[] = 'Erreur lors de la création. Veuillez réessayer.';
        }
        $this->render('entreprise/form', [
            'title'      => 'Ajouter une entreprise',
            'entreprise' => $data,
            'errors'     => $errors,
        ]);
    }
}

    public function editForm(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $entreprise = $this->model->findByIdFull((int) $id);
        if (!$entreprise) { $this->redirect('/entreprises'); return; }
        if (!empty($entreprise['sites'])) {
            $entreprise = array_merge($entreprise, $entreprise['sites'][0]);
        }
        $this->render('entreprise/form', [
            'title'      => 'Modifier l\'entreprise',
            'entreprise' => $entreprise,
            'errors'     => [],
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $this->verifyCsrf();

        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $entreprise = $this->model->findByIdFull((int) $id);
            $this->render('entreprise/form', [
                'title'      => 'Modifier l\'entreprise',
                'entreprise' => array_merge($entreprise ?? [], $data),
                'errors'     => $errors,
            ]);
            return;
        }

        
        try {
    $this->model->update((int) $id, $data);
    // Sites supplémentaires
foreach ($_POST['sites'] ?? [] as $site) {
    if (!empty($site['Adresse'])) {
        $this->model->addSite((int) $id, $site);
    }
}
} catch (\PDOException $e) {
    if (str_contains($e->getMessage(), '1062')) {
        $errors[] = 'Ce numéro SIRET est déjà utilisé par une autre entreprise.';
    } else {
        $errors[] = 'Erreur lors de la mise à jour. Veuillez réessayer.';
    }
    $entreprise = $this->model->findByIdFull((int) $id);
    $this->render('entreprise/form', [
        'title'      => 'Modifier l\'entreprise',
        'entreprise' => array_merge($entreprise ?? [], $data),
        'errors'     => $errors,
    ]);
    return;
}

        $entreprise = $this->model->findByIdFull((int) $id);
        if (!empty($entreprise['sites'])) {
            $this->model->updateSite($entreprise['sites'][0]['Id_site'], [
                'Adresse'     => $data['Adresse'],
                'Ville'       => $data['Ville'],
                'Code_postal' => $data['Code_postal'],
                'Pays'        => $data['Pays'],
            ]);
        } elseif (!empty($data['Adresse'])) {
            $this->model->addSite((int) $id, $data);
        }

        $_SESSION['flash_success'] = 'Entreprise mise à jour.';
        $this->redirect('/entreprises/' . $id);
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $this->model->deleteById((int) $id);
        $_SESSION['flash_success'] = 'Entreprise supprimée.';
        $this->redirect('/entreprises');
    }

    public function noter(string $id): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();
        $this->model->noter(
            (int) $id,
            (int) $_SESSION['user']['id'],
            (int) ($_POST['note'] ?? 1),
            $_POST['commentaire'] ?? ''
        );
        $this->redirect('/entreprises/' . $id);
    }

    protected function getFormData(): array
    {
        return [
            'Nom'              => trim($_POST['Nom']              ?? ''),
            'Description'      => trim($_POST['Description']      ?? ''),
            'Email_contact'    => trim($_POST['Email_contact']    ?? ''),
            'Tel_contact'      => preg_replace('/\D/', '', $_POST['Tel_contact'] ?? ''),
            'statut_juridique' => trim($_POST['statut_juridique'] ?? ''),
            'SIRET'            => preg_replace('/\D/', '', $_POST['SIRET'] ?? ''),
            'Adresse'          => trim($_POST['Adresse']          ?? ''),
            'Ville'            => trim($_POST['Ville']            ?? ''),
            'Code_postal'      => trim($_POST['Code_postal']      ?? ''),
            'Pays'             => trim($_POST['Pays']             ?? 'France'),
        ];
    }

    protected function validate(array $data): array
    {
        $errors = [];
        if (empty($data['Nom']))
            $errors[] = 'La raison sociale est obligatoire.';
        if (empty($data['SIRET']))
            $errors[] = 'Le numéro SIRET est obligatoire.';
        elseif (strlen($data['SIRET']) !== 14)
            $errors[] = 'Le SIRET doit contenir exactement 14 chiffres.';
        if (empty($data['statut_juridique']))
            $errors[] = 'Le statut juridique est obligatoire.';
        if (empty($data['Adresse']))
            $errors[] = 'L\'adresse du siège social est obligatoire.';
        if (empty($data['Ville']))
            $errors[] = 'La ville est obligatoire.';
        if (empty($data['Code_postal']))
            $errors[] = 'Le code postal est obligatoire.';
        if (!empty($data['Tel_contact']) && strlen($data['Tel_contact']) !== 10)
            $errors[] = 'Le téléphone doit contenir 10 chiffres.';
        if (!empty($data['Email_contact']) && !filter_var($data['Email_contact'], FILTER_VALIDATE_EMAIL))
    $errors[] = 'L\'adresse email n\'est pas valide.';
        return $errors;
    }
}
