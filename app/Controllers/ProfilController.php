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

        $candidatures      = [];
        $totalCandidatures = 0;

        if ($role === 'etudiant') {
            $candidatureModel  = new CandidatureModel();
            $totalCandidatures = $candidatureModel->countByEtudiant((int) $_SESSION['user']['id']);
            $candidatures      = $candidatureModel->getByEtudiant((int) $_SESSION['user']['id'], 3, 0);
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

        // ====================== UPLOAD CV (étudiant uniquement) ======================
        if ($_SESSION['user']['role'] === 'etudiant' 
            && !empty($_FILES['cv']['name']) 
            && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {

            $candidatureModel = new CandidatureModel();
            $idEtudiant = $candidatureModel->getIdEtudiant((int) $_SESSION['user']['id']);

            if ($idEtudiant) {
                $uploadDir = '/srv/http/StageLink/uploads/candidatures/' . $idEtudiant . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $res = $this->handleCvUpload($_FILES['cv'], $uploadDir);

                if (isset($res['error'])) {
                    $_SESSION['flash_error'] = 'CV : ' . $res['error'];
                } else {
                    // On garde l'ancien comportement (CV principal)
                    $candidatureModel->saveCv($idEtudiant, $res['nom'], $res['chemin'], true);
                    $_SESSION['flash_success'] = '✅ CV uploadé avec succès !';
                }
            }
        } else {
            $_SESSION['flash_success'] = 'Profil mis à jour.';
        }

        $this->redirect('/profil');
    }

    /**
     * Upload sécurisé du CV
     */
    private function handleCvUpload(array $file, string $dir): array
    {
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'Fichier trop volumineux (max 5 MB).'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($file['tmp_name']) !== 'application/pdf') {
            return ['error' => 'Seuls les fichiers PDF sont acceptés.'];
        }

        $nom    = 'cv_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $chemin = $dir . $nom;

        if (!move_uploaded_file($file['tmp_name'], $chemin)) {
            return ['error' => 'Impossible de sauvegarder le fichier.'];
        }

        return ['nom' => $nom, 'chemin' => $chemin];
    }

    // Ancienne méthode photo (gardée pour éviter les erreurs)
    public function servirPhoto(string $fichier): void
    {
        http_response_code(404);
        exit;
    }
}