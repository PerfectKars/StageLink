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
        $user      = $userModel->findById((int) $_SESSION['user']['id']);
        $role      = $_SESSION['user']['role'] ?? '';

        if ($role === 'etudiant' && empty($_SESSION['user']['photo'])) {
    $idEtudiant = (new \App\Models\CandidatureModel())->getIdEtudiant(
        (int) $_SESSION['user']['id']
    );
    if ($idEtudiant) {
        $photo = (new \App\Models\EtudiantModel())->getPhoto($idEtudiant);
        if ($photo) $_SESSION['user']['photo'] = $photo;
    }
}

        $candidatures      = [];
        $totalCandidatures = 0;

        if ($role === 'etudiant') {
        $candidatureModel  = new CandidatureModel();
        $toutes            = $candidatureModel->getByEtudiant((int) $_SESSION['user']['id']);
        $totalCandidatures = count($toutes);
        $candidatures      = array_slice($toutes, 0, 5);
    }

        if ($_SESSION['user']['role'] === 'etudiant') {
            $candidatureModel  = new CandidatureModel();
            $toutes            = $candidatureModel->getByEtudiant((int) $_SESSION['user']['id']);
            $totalCandidatures = count($toutes);
            $candidatures      = array_slice($toutes, 0, 5); // 5 dernières seulement
        }

        $this->render('profil/index', [
            'title'             => 'Mon profil',
            'user'              => $user,
            'candidatures'      => $candidatures,
            'totalCandidatures' => $totalCandidatures,
        ]);
    }

    public function update(): void
{
    $this->requireAuth();
    $this->verifyCsrf();

    $userModel = new UserModel();
    $data = [
        'nom'          => trim($_POST['nom']          ?? ''),
        'prenom'       => trim($_POST['prenom']       ?? ''),
        'email'        => trim($_POST['email']        ?? ''),
        'telephone'    => trim($_POST['telephone']    ?? ''),
        'mot_de_passe' => $_POST['mot_de_passe']     ?? '',
    ];

    $userModel->update((int) $_SESSION['user']['id'], $data);

    // Mise à jour session
    $_SESSION['user']['nom']    = $data['nom'];
    $_SESSION['user']['prenom'] = $data['prenom'];
    $_SESSION['user']['email']  = $data['email'];

    // Upload photo (étudiant uniquement)
    if ($_SESSION['user']['role'] === 'etudiant'
        && !empty($_FILES['photo']['name'])
        && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($_FILES['photo']['tmp_name']);

        if (in_array($mime, $allowed) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            $ext        = match($mime) {
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $idEtudiant = (new \App\Models\CandidatureModel())->getIdEtudiant(
                (int) $_SESSION['user']['id']
            );
            $nomFichier = 'photo_' . $idEtudiant . '.' . $ext;
            $chemin     = '/srv/http/StageLink/uploads/photos/' . $nomFichier;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $chemin)) {
                (new \App\Models\EtudiantModel())->updatePhoto($idEtudiant, $nomFichier);
                $_SESSION['user']['photo'] = $nomFichier;
            }
        } else {
            $_SESSION['flash_error'] = 'Photo invalide (JPG/PNG/WEBP, max 2MB).';
        }
    }

    $_SESSION['flash_success'] = 'Profil mis à jour.';
    $this->redirect('/profil');
}

public function servirPhoto(string $fichier): void
{
    // Sécurité : pas de path traversal
    $fichier = basename($fichier);
    $chemin  = '/srv/http/StageLink/uploads/photos/' . $fichier;

    if (!file_exists($chemin)) {
        http_response_code(404);
        exit;
    }

    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($chemin);
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($chemin);
    exit;
}

}
