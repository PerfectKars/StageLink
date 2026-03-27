<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function loginForm(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/');
        }
        $this->render('auth/login', ['title' => 'Connexion']);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        $user = $this->userModel->authenticate($email, $password);
        // DEBUG TEMPORAIRE — à supprimer après
$raw = $this->userModel->findByEmail($email);
error_log("USER FOUND: " . json_encode($raw ? array_keys($raw) : 'NOT FOUND'));
error_log("VERIFY: " . var_export($raw ? password_verify($password, $raw['Mot_de_passe']) : false, true));

        if (!$user) {
            $this->render('auth/login', [
                'title' => 'Connexion',
                'error' => 'Email ou mot de passe incorrect.',
            ]);
            return;
        }

        // Régénération de l'ID de session (prévention fixation de session)
        session_regenerate_id(true);

        // Stockage session — clés normalisées en minuscules
        $_SESSION['user'] = [
            'id'     => (int) $user['Id_utilisateur'],
            'nom'    => $user['nom']    ?? '',
            'prenom' => $user['prenom'] ?? '',
            'email'  => $user['Email'],
            'role'   => $user['Role'],   // 'admin' | 'pilote' | 'etudiant'
        ];

        $this->redirect('/');
    }

    public function logout(): void
    {
        session_destroy();
        header('Clear-Site-Data: "cache", "cookies"');
        $this->redirect('/login');
    }
}
