<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\CandidatureModel;
use App\Models\OffreModel;

class CandidatureController extends BaseController
{
    private CandidatureModel $candidatureModel;
    private OffreModel $offreModel;

    private const UPLOAD_DIR    = '/srv/http/StageLink/uploads/candidatures/';
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_TYPES = ['application/pdf'];

    public function __construct()
    {
        $this->candidatureModel = new CandidatureModel();
        $this->offreModel       = new OffreModel();
    }

    /**
     * GET /offres/:id/postuler
     */
    public function postulerForm(string $idOffre): void
    {
        $this->requireRole('etudiant');

        $idOffre = (int) $idOffre;
        $offre   = $this->offreModel->findByIdFull($idOffre);

        if (!$offre) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Offre introuvable']);
            return;
        }

        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);

        if ($this->candidatureModel->aDejaPostule($idUtilisateur, $idOffre)) {
            $_SESSION['flash_info'] = 'Vous avez déjà postulé à cette offre.';
            $this->redirect('/offres/' . $idOffre);
            return;
        }

        $cvExistants = $this->candidatureModel->getCvEtudiant($idUtilisateur);

        $this->render('candidature/postuler', [
            'title'       => 'Postuler — ' . $offre['Titre'],
            'offre'       => $offre,
            'cvExistants' => $cvExistants,
        ]);
    }

    /**
     * POST /offres/:id/postuler
     */
    public function postuler(string $idOffre): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();

        $idOffre       = (int) $idOffre;
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $lettre        = trim($_POST['lettre_motivation'] ?? '');
        $idCvExistant  = (int) ($_POST['id_cv_existant'] ?? 0);
        $errors        = [];

        if ($this->candidatureModel->aDejaPostule($idUtilisateur, $idOffre)) {
            $_SESSION['flash_error'] = 'Vous avez déjà postulé à cette offre.';
            $this->redirect('/offres/' . $idOffre);
            return;
        }

        $idEtudiant = $this->candidatureModel->getIdEtudiant($idUtilisateur);
        if (!$idEtudiant) {
            $_SESSION['flash_error'] = 'Erreur : profil étudiant introuvable.';
            $this->redirect('/offres/' . $idOffre);
            return;
        }

        $uploadDir = self::UPLOAD_DIR . $idEtudiant . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // ── CV ────────────────────────────────────────────────────────────────
        $idCv = $idCvExistant ?: null;

        if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
            $res = $this->handleUpload($_FILES['cv'], $uploadDir, 'cv');
            if (isset($res['error'])) {
                $errors[] = 'CV : ' . $res['error'];
            } else {
                $idCv = $this->candidatureModel->saveCv(
                    $idEtudiant, $res['nom'], $res['chemin'], $idCvExistant === 0
                );
            }
        } elseif (!$idCvExistant) {
            $errors[] = 'Veuillez fournir un CV (PDF obligatoire).';
        }

        // ── Lettre de motivation PDF (optionnel) ──────────────────────────────
        $cheminLm = null;
        if (!empty($_FILES['lm']['name']) && $_FILES['lm']['error'] !== UPLOAD_ERR_NO_FILE) {
            $res = $this->handleUpload($_FILES['lm'], $uploadDir, 'lm');
            if (isset($res['error'])) {
                $errors[] = 'Lettre de motivation : ' . $res['error'];
            } else {
                $cheminLm = $res['chemin'];
            }
        }

        // ── Autres documents (optionnel, multiple) ────────────────────────────
        $autresChemins = [];
        if (!empty($_FILES['autres']['name'][0])) {
            foreach ($_FILES['autres']['name'] as $i => $nom) {
                if ($_FILES['autres']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $file = [
                    'name'     => $_FILES['autres']['name'][$i],
                    'type'     => $_FILES['autres']['type'][$i],
                    'tmp_name' => $_FILES['autres']['tmp_name'][$i],
                    'error'    => $_FILES['autres']['error'][$i],
                    'size'     => $_FILES['autres']['size'][$i],
                ];
                $res = $this->handleUpload($file, $uploadDir, 'doc_' . ($i + 1));
                if (isset($res['error'])) {
                    $errors[] = 'Document ' . ($i + 1) . ' : ' . $res['error'];
                } else {
                    $autresChemins[] = $res['chemin'];
                }
            }
        }

        // ── Erreurs → réafficher formulaire ──────────────────────────────────
        if (!empty($errors)) {
            $offre       = $this->offreModel->findByIdFull($idOffre);
            $cvExistants = $this->candidatureModel->getCvEtudiant($idUtilisateur);
            $this->render('candidature/postuler', [
                'title'       => 'Postuler — ' . ($offre['Titre'] ?? ''),
                'offre'       => $offre,
                'cvExistants' => $cvExistants,
                'errors'      => $errors,
                'lettre'      => $lettre,
            ]);
            return;
        }

        // ── Insertion ─────────────────────────────────────────────────────────
        $ok = $this->candidatureModel->postuler(
            $idUtilisateur, $idOffre, $lettre, $idCv, $cheminLm, $autresChemins
        );

        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $ok
            ? '✅ Candidature envoyée avec succès !'
            : 'Une erreur est survenue. Veuillez réessayer.';

        $this->redirect('/offres/' . $idOffre);
    }

    /**
     * GET /mes-candidatures
     */
    public function mesCandidatures(): void
    {
        $this->requireRole('etudiant');
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $candidatures  = $this->candidatureModel->getByEtudiant($idUtilisateur);
        $this->render('candidature/index', [
            'title'        => 'Mes candidatures',
            'candidatures' => $candidatures,
        ]);
    }

    /**
     * Upload sécurisé — PDF uniquement, max 5MB.
     */
    private function handleUpload(array $file, string $dir, string $prefix): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Erreur lors du téléchargement (code ' . $file['error'] . ').'];
        }
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['error' => 'Fichier trop volumineux (max 5 MB).'];
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            return ['error' => 'Seuls les fichiers PDF sont acceptés.'];
        }
        $nomFichier = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $chemin     = $dir . $nomFichier;
        if (!move_uploaded_file($file['tmp_name'], $chemin)) {
            return ['error' => 'Impossible de sauvegarder le fichier.'];
        }
        return ['nom' => $nomFichier, 'chemin' => $chemin];
    }
}
