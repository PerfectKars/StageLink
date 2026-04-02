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

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $user = $this->userModel->authenticate($email, $password);

    if (!$user) {
        $this->render('auth/login', [
            'title' => 'Connexion',
            'error' => 'Email ou mot de passe incorrect.',
        ]);
        return;
    }

    // Régénération de l'ID de session (sécurité)
    session_regenerate_id(true);

    // Stockage session de base
    $_SESSION['user'] = [
        'id'          => (int) $user['Id_utilisateur'],
        'id_etudiant' => null,                    // ← important
        'nom'         => $user['nom'] ?? '',
        'prenom'      => $user['prenom'] ?? '',
        'email'       => $user['Email'],
        'role'        => $user['Role'],
    ];

    // Si c'est un étudiant, on récupère son Id_etudiant
    if ($user['Role'] === 'etudiant') {
        $db = \App\Core\Database::getInstance();

        $stmt = $db->prepare("
            SELECT Id_etudiant 
            FROM ETUDIANT 
            WHERE Id_utilisateur = :id_utilisateur
            LIMIT 1
        ");
        $stmt->execute([':id_utilisateur' => $user['Id_utilisateur']]);
        $idEtudiant = $stmt->fetchColumn();

        if ($idEtudiant !== false) {
            $_SESSION['user']['id_etudiant'] = (int) $idEtudiant;
        }
    }

    $_SESSION['flash_success'] = 'Connexion réussie !';
    $this->redirect('/');
}

public function logout(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    // Nettoyage des cookies de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    header('Clear-Site-Data: "cache", "cookies"');
    $this->redirect('/login');
}

}
