<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\PiloteModel;
use App\Models\EtudiantModel;

class AdminController extends BaseController
{
    private PiloteModel $piloteModel;
    private EtudiantModel $etudiantModel;

    public function __construct()
    {
        $this->piloteModel   = new PiloteModel();
        $this->etudiantModel = new EtudiantModel();
    }

    /**
     * GET /admin/utilisateurs
     * Page de choix du type d'utilisateur.
     */
    public function utilisateurs(): void
    {
        $this->requireRole('admin');

        $nbPilotes   = $this->piloteModel->count();
        $nbEtudiants = $this->etudiantModel->count();

        $this->render('admin/utilisateurs/index', [
            'title'       => 'Gestion des utilisateurs',
            'nbPilotes'   => $nbPilotes,
            'nbEtudiants' => $nbEtudiants,
        ]);
    }

    /**
     * GET /admin/utilisateurs/creer?type=pilote|etudiant
     * Formulaire de création d'utilisateur.
     */
    public function creerForm(): void
    {
        $this->requireRole('admin');
        $type = $_GET['type'] ?? 'etudiant';

        if (!in_array($type, ['pilote', 'etudiant'])) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $promotions = $this->etudiantModel->getPromotions();

        $this->render('admin/utilisateurs/creer', [
            'title'      => 'Créer un ' . $type,
            'type'       => $type,
            'promotions' => $promotions,
            'errors'     => [],
        ]);
    }

    /**
     * POST /admin/utilisateurs/creer
     * Traitement création utilisateur.
     */
    public function creer(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $type   = $_POST['type'] ?? 'etudiant';
        $data   = $this->getFormData();
        $errors = $this->validate($data, $type);

        if (!empty($errors)) {
            $promotions = $this->etudiantModel->getPromotions();
            $this->render('admin/utilisateurs/creer', [
                'title'      => 'Créer un ' . $type,
                'type'       => $type,
                'promotions' => $promotions,
                'errors'     => $errors,
                'data'       => $data,
            ]);
            return;
        }

        try {
            if ($type === 'pilote') {
                $this->piloteModel->createPilote($data);
                $_SESSION['flash_success'] = 'Pilote créé avec succès.';
                $this->redirect('/admin/pilotes');
            } else {
                $this->etudiantModel->createEtudiant($data);
                $_SESSION['flash_success'] = 'Étudiant créé avec succès.';
                $this->redirect('/admin/etudiants');
            }
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), '1062')) {
                $errors[] = 'Cet email est déjà utilisé.';
            } else {
                $errors[] = 'Erreur lors de la création.';
            }
            $promotions = $this->etudiantModel->getPromotions();
            $this->render('admin/utilisateurs/creer', [
                'title'      => 'Créer un ' . $type,
                'type'       => $type,
                'promotions' => $promotions,
                'errors'     => $errors,
                'data'       => $data,
            ]);
        }
    }

    protected function getFormData(): array
    {
        return [
            'nom'          => trim($_POST['nom']          ?? ''),
            'prenom'       => trim($_POST['prenom']       ?? ''),
            'email'        => trim($_POST['email']        ?? ''),
            'telephone'    => preg_replace('/\D/', '', $_POST['telephone'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe']     ?? '',
            'id_promotion' => (int) ($_POST['id_promotion'] ?? 0),
        ];
    }

    protected function validate(array $data, string $type): array
    {
        $errors = [];
        if (empty($data['nom']))    $errors[] = 'Le nom est obligatoire.';
        if (empty($data['prenom'])) $errors[] = 'Le prénom est obligatoire.';
        if (empty($data['email']))  $errors[] = 'L\'email est obligatoire.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email invalide.';
        if (empty($data['mot_de_passe']))
            $errors[] = 'Le mot de passe est obligatoire.';
        elseif (strlen($data['mot_de_passe']) < 8)
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        if ($type === 'etudiant' && $data['id_promotion'] === 0)
            $errors[] = 'Veuillez sélectionner une promotion.';
        return $errors;
    }

    /** GET /admin/promotions/create */
public function promotionForm(): void
{
    $this->requireRole('admin');
    $pilotes = $this->piloteModel->findAll(100);
    $this->render('admin/promotions/form', [
        'title'   => 'Créer une promotion',
        'pilotes' => $pilotes,
        'errors'  => [],
    ]);
}

/** POST /admin/promotions/create */
public function promotionCreate(): void
{
    $this->requireRole('admin');
    $this->verifyCsrf();

    $data = [
        'libelle'   => trim($_POST['libelle']    ?? ''),
        'annee' => trim($_POST['annee'] ?? ''),
        'filiere'   => trim($_POST['filiere']    ?? ''),
        'id_pilote' => (int) ($_POST['id_pilote'] ?? 0),
    ];
    $errors = [];

    if (empty($data['libelle'])) $errors[] = 'Le libellé est obligatoire.';
    if (empty($data['filiere'])) $errors[] = 'La filière est obligatoire.';
    if (empty($data['annee'])) $errors[] = 'L\'année scolaire est obligatoire.';

    if (!empty($errors)) {
        $this->render('admin/promotions/form', [
            'title'   => 'Créer une promotion',
            'pilotes' => $this->piloteModel->findAll(100),
            'errors'  => $errors,
            'data'    => $data,
        ]);
        return;
    }

    $this->piloteModel->createPromotion($data);
    $_SESSION['flash_success'] = 'Promotion créée avec succès.';
    $this->redirect('/admin/utilisateurs');
}

public function promotionDetail(string $id): void
{
    $this->requireRole('admin');
    $idPromotion = (int) $id;
    $promotion   = $this->piloteModel->getPromotion($idPromotion);

    if (!$promotion) {
        http_response_code(404);
        $this->render('error/404', ['title' => 'Promotion introuvable']);
        return;
    }

    $etudiants = $this->piloteModel->getEtudiants($idPromotion);

    $this->render('admin/promotions/detail', [
        'title'      => $promotion['Libelle'],
        'promotion'  => $promotion,
        'etudiants'  => $etudiants,
    ]);
}

}
