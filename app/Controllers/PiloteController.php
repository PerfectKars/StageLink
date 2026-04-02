<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\PiloteModel;

class PiloteController extends BaseController
{
    private PiloteModel $piloteModel;

    public function __construct()
    {
        $this->piloteModel = new PiloteModel();
    }

    /**
     * GET /pilote/promotions
     * Liste des promotions du pilote connecté.
     */
    public function promotions(): void
{
    $this->requireRole('pilote');
    $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
    $page          = max(1, (int) ($_GET['page'] ?? 1));
    $perPage       = 10;
    $promotions    = $this->piloteModel->getPromotions($idUtilisateur, $perPage, ($page - 1) * $perPage);
    $total         = $this->piloteModel->countPromotions($idUtilisateur);

    $this->render('pilote/promotions', [
        'title'      => 'Mes promotions',
        'promotions' => $promotions,
        'page'       => $page,
        'perPage'    => $perPage,
        'total'      => $total,
    ]);
}

    /**
     * GET /pilote/promotions/:id
     * Liste des étudiants d'une promotion.
     */
    public function promotion(string $idPromotion): void
    {
        $this->requireRole('pilote');
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $idPromotion   = (int) $idPromotion;

        // Vérifier que cette promotion appartient bien au pilote
        if (!$this->piloteModel->promotionAppartientAuPilote($idUtilisateur, $idPromotion)) {
            http_response_code(403);
            $this->render('error/403', ['title' => 'Accès refusé']);
            return;
        }

        $promotion = $this->piloteModel->getPromotion($idPromotion);
        $etudiants = $this->piloteModel->getEtudiants($idPromotion);

        $this->render('pilote/promotion', [
            'title'      => $promotion['Libelle'] ?? 'Promotion',
            'promotion'  => $promotion,
            'etudiants'  => $etudiants,
        ]);
    }

    /**
     * GET /pilote/etudiants/:id
     * Profil complet d'un étudiant (CV, LM, candidatures).
     */
    public function etudiant(string $idEtudiant): void
    {
        $this->requireRole('pilote');
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $idEtudiant    = (int) $idEtudiant;

        // Vérifier que l'étudiant est dans une promotion du pilote
        if (!$this->piloteModel->etudiantDuPilote($idUtilisateur, $idEtudiant)) {
            http_response_code(403);
            $this->render('error/403', ['title' => 'Accès refusé']);
            return;
        }

        $etudiant     = $this->piloteModel->getEtudiantProfil($idEtudiant);
        $candidatures = $this->piloteModel->getCandidatures($idEtudiant);
        $cvs          = $this->piloteModel->getCvs($idEtudiant);

        $this->render('pilote/etudiant', [
            'title'        => ($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? ''),
            'etudiant'     => $etudiant,
            'candidatures' => $candidatures,
            'cvs'          => $cvs,
        ]);
    }

    // ── CRUD admin (PiloteController sert aussi pour /admin/pilotes) ──────────

    private const PER_PAGE = 7;

public function index(): void
{
    $this->requireRole('admin');
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $pilotes = $this->piloteModel->findAll(self::PER_PAGE, ($page - 1) * self::PER_PAGE);
    $total   = $this->piloteModel->count();
    $this->render('admin/pilotes/index', [
        'title'   => 'Gestion des pilotes',
        'pilotes' => $pilotes,
        'page'    => $page,
        'perPage' => self::PER_PAGE,
        'total'   => $total,
    ]);
}

    public function show(string $id): void
    {
        $this->requireRole('admin');
        $pilote = $this->piloteModel->findByIdFull((int) $id);
        $this->render('admin/pilotes/show', [
            'title'  => $pilote['prenom'] . ' ' . $pilote['nom'],
            'pilote' => $pilote,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole('admin');
        $this->render('admin/pilotes/form', [
            'title'  => 'Créer un pilote',
            'pilote' => null,
            'errors' => [],
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('admin/pilotes/form', [
                'title'  => 'Créer un pilote',
                'pilote' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->piloteModel->createPilote($data);
        $_SESSION['flash_success'] = 'Pilote créé avec succès.';
        $this->redirect('/admin/pilotes');
    }

    public function editForm(string $id): void
    {
        $this->requireRole('admin');
        $pilote = $this->piloteModel->findByIdFull((int) $id);
        $this->render('admin/pilotes/form', [
            'title'  => 'Modifier le pilote',
            'pilote' => $pilote,
            'errors' => [],
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $data = $this->getFormData();
        $this->piloteModel->updatePilote((int) $id, $data);
        $_SESSION['flash_success'] = 'Pilote mis à jour.';
        $this->redirect('/admin/pilotes');
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $this->piloteModel->deletePilote((int) $id);
        $_SESSION['flash_success'] = 'Pilote supprimé.';
        $this->redirect('/admin/pilotes');
    }

    protected function getFormData(): array
    {
        return [
            'nom'          => trim($_POST['nom']          ?? ''),
            'prenom'       => trim($_POST['prenom']       ?? ''),
            'email'        => trim($_POST['email']        ?? ''),
            'telephone'    => preg_replace('/\D/', '', $_POST['telephone'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe']     ?? '',
        ];
    }

    protected function validate(array $data): array
    {
        $errors = [];
        if (empty($data['nom']))    $errors[] = 'Le nom est obligatoire.';
        if (empty($data['prenom'])) $errors[] = 'Le prénom est obligatoire.';
        if (empty($data['email']))  $errors[] = 'L\'email est obligatoire.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email invalide.';
        if (empty($data['mot_de_passe'])) $errors[] = 'Le mot de passe est obligatoire.';
        return $errors;
    }
}
