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
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_TYPES = ['application/pdf'];

    public function __construct()
    {
        $this->candidatureModel = new CandidatureModel();
        $this->offreModel       = new OffreModel();
    }

    /** GET /offres/:id/postuler */
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

        $this->render('candidature/postuler', [
            'title'       => 'Postuler — ' . $offre['Titre'],
            'offre'       => $offre,
            'cvExistants' => $this->candidatureModel->getCvEtudiant($idUtilisateur),
        ]);
    }

    /** POST /offres/:id/postuler */
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
            $_SESSION['flash_error'] = 'Profil étudiant introuvable.';
            $this->redirect('/offres/' . $idOffre);
            return;
        }

        $uploadDir = self::UPLOAD_DIR . $idEtudiant . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $idCv = $idCvExistant ?: null;

        if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
            $res = $this->handleUpload($_FILES['cv'], $uploadDir, 'cv');
            if (isset($res['error'])) {
                $errors[] = 'CV : ' . $res['error'];
            } else {
                $idCv = $this->candidatureModel->saveCv($idEtudiant, $res['nom'], $res['chemin'], $idCvExistant === 0);
            }
        } elseif (!$idCvExistant) {
            $errors[] = 'Veuillez fournir un CV (PDF obligatoire).';
        }

        $cheminLm = null;
        if (!empty($_FILES['lm']['name']) && $_FILES['lm']['error'] !== UPLOAD_ERR_NO_FILE) {
            $res = $this->handleUpload($_FILES['lm'], $uploadDir, 'lm');
            if (isset($res['error'])) $errors[] = 'LM : ' . $res['error'];
            else $cheminLm = $res['chemin'];
        }

        $autresChemins = [];
        if (!empty($_FILES['autres']['name'][0])) {
            foreach ($_FILES['autres']['name'] as $i => $nom) {
                if ($_FILES['autres']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $file = ['name' => $_FILES['autres']['name'][$i], 'type' => $_FILES['autres']['type'][$i],
                         'tmp_name' => $_FILES['autres']['tmp_name'][$i], 'error' => $_FILES['autres']['error'][$i],
                         'size' => $_FILES['autres']['size'][$i]];
                $res = $this->handleUpload($file, $uploadDir, 'doc_' . ($i + 1));
                if (isset($res['error'])) $errors[] = 'Document ' . ($i + 1) . ' : ' . $res['error'];
                else $autresChemins[] = $res['chemin'];
            }
        }

        if (!empty($errors)) {
            $this->render('candidature/postuler', [
                'title'       => 'Postuler',
                'offre'       => $this->offreModel->findByIdFull($idOffre),
                'cvExistants' => $this->candidatureModel->getCvEtudiant($idUtilisateur),
                'errors'      => $errors,
                'lettre'      => $lettre,
            ]);
            return;
        }

        $ok = $this->candidatureModel->postuler($idUtilisateur, $idOffre, $lettre, $idCv, $cheminLm, $autresChemins);
        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $ok
            ? '✅ Candidature envoyée !'
            : 'Une erreur est survenue.';
        $this->redirect('/offres/' . $idOffre);
    }

    /** GET /mes-candidatures */
    public function mesCandidatures(): void
    {
        $this->requireRole('etudiant');
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $this->render('candidature/index', [
            'title'        => 'Mes candidatures',
            'candidatures' => $this->candidatureModel->getByEtudiant($idUtilisateur),
        ]);
    }

    /** GET /pilote/candidatures */
    public function candidaturesPromotion(): void
{
    $this->requireRole('pilote');
    $this->redirect('/pilote/promotions');
}

    /** GET /pilote/candidatures/:id (Id_etudiant) */
    public function detailCandidature(string $idEtudiant): void
    {
        $this->requireRole('pilote');
        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $idEtudiant    = (int) $idEtudiant;

        if (!$this->candidatureModel->etudiantDansPromotion($idUtilisateur, $idEtudiant)) {
            http_response_code(403);
            $this->render('error/403', ['title' => 'Accès refusé']);
            return;
        }

        $this->render('candidature/detail_etudiant', [
            'title'        => 'Candidatures de l\'étudiant',
            'etudiant'     => $this->candidatureModel->getEtudiantInfo($idEtudiant),
            'candidatures' => $this->candidatureModel->getByEtudiantId($idEtudiant),
        ]);
    }

    private function handleUpload(array $file, string $dir, string $prefix): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Erreur upload (code ' . $file['error'] . ').'];
        if ($file['size'] > self::MAX_FILE_SIZE) return ['error' => 'Fichier trop volumineux (max 5 MB).'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_TYPES, true)) return ['error' => 'PDF uniquement.'];
        $nom    = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $chemin = $dir . $nom;
        if (!move_uploaded_file($file['tmp_name'], $chemin)) return ['error' => 'Impossible de sauvegarder.'];
        return ['nom' => $nom, 'chemin' => $chemin];
    }

    /**
     * GET /pilote/cv/:idOffre/:idEtudiant
     * Sert le fichier CV de façon sécurisée (hors webroot).
     */
    public function telechargerCv(string $idOffre, string $idEtudiant): void
    {
        $this->requireRole('pilote', 'admin');

        $idOffre    = (int) $idOffre;
        $idEtudiant = (int) $idEtudiant;
        $idUtil     = (int) ($_SESSION['user']['id'] ?? 0);

        // Vérifier que l'étudiant appartient à la promotion du pilote (sauf admin)
        if ($_SESSION['user']['role'] === 'pilote') {
            if (!$this->candidatureModel->etudiantDansPromotion($idUtil, $idEtudiant)) {
                http_response_code(403);
                $this->render('error/403', ['title' => 'Accès refusé']);
                return;
            }
        }

        // Récupérer le chemin du CV depuis la candidature
        $stmt = $this->candidatureModel->getCvCandidature($idOffre, $idEtudiant);

        if (!$stmt || empty($stmt['cv_chemin'])) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Fichier introuvable']);
            return;
        }

        $chemin = $stmt['cv_chemin'];

        if (!file_exists($chemin) || !is_readable($chemin)) {
            http_response_code(404);
            $this->render('error/404', ['title' => 'Fichier introuvable']);
            return;
        }

        // Servir le fichier
        $nomFichier = $stmt['cv_nom'] ?? 'cv.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($nomFichier) . '"');
        header('Content-Length: ' . filesize($chemin));
        header('Cache-Control: private, no-cache');
        readfile($chemin);
        exit;
    }
}
