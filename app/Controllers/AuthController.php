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

    /**
     * Affiche le formulaire de connexion
     */
    public function loginForm(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/');
        }

        $this->render('auth/login', ['title' => 'Connexion']);
    }

    /**
     * Traite la connexion
     */
    public function login(): void
    {
        // Vérification CSRF
        if (!$this->verifyCsrf()) {
            $this->render('auth/login', [
                'title' => 'Connexion',
                'error' => 'Token de sécurité invalide. Réessayez.',
            ]);
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->render('auth/login', [
                'title' => 'Connexion',
                'error' => 'Veuillez remplir tous les champs.',
            ]);
            return;
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $this->render('auth/login', [
                'title' => 'Connexion',
                'error' => 'Email ou mot de passe incorrect.',
            ]);
            return;
        }

        // Régénération de l'ID de session (prévention fixation de session)
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $user['id_utilisateur'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'email'  => $user['email'],
            'role'   => $user['role'],
        ];

        $this->redirect('/');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    /**
     * Vérifie le token CSRF
     */
    private function verifyCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
