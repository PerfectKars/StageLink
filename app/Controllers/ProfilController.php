<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\UserModel;
use App\Models\CandidatureModel;

class ProfilController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $userModel = new UserModel();
        $user = $userModel->findById((int) $_SESSION['user']['id']);

        $candidatures = [];
        $wishlist = [];

        if ($_SESSION['user']['role'] === 'etudiant') {
            $candidatureModel = new CandidatureModel();
            $candidatures = $candidatureModel->getByEtudiant((int) $_SESSION['user']['id']);
        }

        $this->render('profil/index', [
            'title'        => 'Mon profil',
            'user'         => $user,
            'candidatures' => $candidatures,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();

        $userModel = new UserModel();
        $data = [
            'nom'       => trim($_POST['nom'] ?? ''),
            'prenom'    => trim($_POST['prenom'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        ];

        $userModel->update((int) $_SESSION['user']['id'], $data);

        // Mettre à jour la session
        $_SESSION['user']['nom']    = $data['nom'];
        $_SESSION['user']['prenom'] = $data['prenom'];
        $_SESSION['user']['email']  = $data['email'];

        $this->redirect('/profil');
    }
}
