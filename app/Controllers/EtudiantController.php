<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EtudiantModel;

class EtudiantController extends BaseController
{
    private EtudiantModel $etudiantModel;

    public function __construct()
    {
        $this->etudiantModel = new EtudiantModel();
    }

    /** GET /admin/etudiants */
    public function index(): void
    {
        $this->requireRole('admin', 'pilote');
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $search    = trim($_GET['search'] ?? '');
        $etudiants = $this->etudiantModel->search($search, $page, 20);
        $total     = $this->etudiantModel->count();

        $this->render('admin/etudiants/index', [
            'title'     => 'Gestion des étudiants',
            'etudiants' => $etudiants,
            'search'    => $search,
            'page'      => $page,
            'total'     => $total,
        ]);
    }

    /** GET /admin/etudiants/:id */
    public function show(string $id): void
    {
        $this->requireRole('admin', 'pilote');
        $etudiant = $this->etudiantModel->findByIdFull((int) $id);

        if (!$etudiant) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Étudiant introuvable']);
            return;
        }

        $this->render('admin/etudiants/show', [
            'title'    => $etudiant['prenom'] . ' ' . $etudiant['nom'],
            'etudiant' => $etudiant,
        ]);
    }

    /** GET /admin/etudiants/create */
    public function createForm(): void
    {
        $this->requireRole('admin');
        $promotions = $this->etudiantModel->getPromotions();
        $this->render('admin/etudiants/form', [
            'title'      => 'Créer un étudiant',
            'etudiant'   => null,
            'promotions' => $promotions,
            'errors'     => [],
        ]);
    }

    /** POST /admin/etudiants/create */
    public function create(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('admin/etudiants/form', [
                'title'      => 'Créer un étudiant',
                'etudiant'   => $data,
                'promotions' => $this->etudiantModel->getPromotions(),
                'errors'     => $errors,
            ]);
            return;
        }

        try {
            $this->etudiantModel->createEtudiant($data);
            $_SESSION['flash_success'] = 'Étudiant créé avec succès.';
            $this->redirect('/admin/etudiants');
        } catch (\PDOException $e) {
            $errors[] = str_contains($e->getMessage(), '1062')
                ? 'Cet email est déjà utilisé.'
                : 'Erreur lors de la création.';
            $this->render('admin/etudiants/form', [
                'title'      => 'Créer un étudiant',
                'etudiant'   => $data,
                'promotions' => $this->etudiantModel->getPromotions(),
                'errors'     => $errors,
            ]);
        }
    }

    /** GET /admin/etudiants/:id/edit */
    public function editForm(string $id): void
    {
        $this->requireRole('admin');
        $etudiant   = $this->etudiantModel->findByIdFull((int) $id);
        $promotions = $this->etudiantModel->getPromotions();
        $this->render('admin/etudiants/form', [
            'title'      => 'Modifier l\'étudiant',
            'etudiant'   => $etudiant,
            'promotions' => $promotions,
            'errors'     => [],
        ]);
    }

    /** POST /admin/etudiants/:id/edit */
    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $data   = $this->getFormData();
        $errors = $this->validate($data, false);

        if (!empty($errors)) {
            $this->render('admin/etudiants/form', [
                'title'      => 'Modifier l\'étudiant',
                'etudiant'   => $data,
                'promotions' => $this->etudiantModel->getPromotions(),
                'errors'     => $errors,
            ]);
            return;
        }

        $this->etudiantModel->updateEtudiant((int) $id, $data);
        $_SESSION['flash_success'] = 'Étudiant mis à jour.';
        $this->redirect('/admin/etudiants/' . $id);
    }

    /** POST /admin/etudiants/:id/delete */
    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $this->etudiantModel->deleteEtudiant((int) $id);
        $_SESSION['flash_success'] = 'Étudiant supprimé.';
        $this->redirect('/admin/etudiants');
    }

    protected function getFormData(): array
    {
        return [
            'nom'             => trim($_POST['nom']             ?? ''),
            'prenom'          => trim($_POST['prenom']          ?? ''),
            'email'           => trim($_POST['email']           ?? ''),
            'telephone'       => preg_replace('/\D/', '', $_POST['telephone'] ?? ''),
            'mot_de_passe'    => $_POST['mot_de_passe']        ?? '',
            'statut_recherche'=> trim($_POST['statut_recherche'] ?? ''),
            'id_promotion'    => (int) ($_POST['id_promotion']  ?? 0),
        ];
    }

    protected function validate(array $data, bool $creation = true): array
    {
        $errors = [];
        if (empty($data['nom']))    $errors[] = 'Le nom est obligatoire.';
        if (empty($data['prenom'])) $errors[] = 'Le prénom est obligatoire.';
        if (empty($data['email']))  $errors[] = 'L\'email est obligatoire.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email invalide.';
        if ($creation && empty($data['mot_de_passe']))
            $errors[] = 'Le mot de passe est obligatoire.';
        if (!empty($data['mot_de_passe']) && strlen($data['mot_de_passe']) < 8)
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        return $errors;
    }
}
