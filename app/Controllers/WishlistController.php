<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\WishlistModel;

class WishlistController extends BaseController
{
    private WishlistModel $wishlistModel;

    public function __construct()
    {
        $this->wishlistModel = new WishlistModel();
    }

    /** GET /wishlist — affiche la wishlist de l'étudiant connecté */
    public function index(): void
    {
        $this->requireRole('etudiant');
        $idEtudiant = $_SESSION['user_id'] ?? 0;
        $offres     = $this->wishlistModel->findByEtudiant((int) $idEtudiant);

        $this->render('wishlist/index', [
            'title'  => 'Ma liste de souhaits',
            'offres' => $offres,
        ]);
    }

    /** POST /wishlist/add — ajoute une offre à la wishlist */
    public function add(): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();

        $idEtudiant = (int) ($_SESSION['user_id'] ?? 0);
        $idOffre    = (int) ($_POST['id_offre'] ?? 0);

        if ($idOffre > 0) {
            $this->wishlistModel->add($idEtudiant, $idOffre);
        }

        $this->redirect('/wishlist');
    }

    /** POST /wishlist/remove — retire une offre de la wishlist */
    public function remove(): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();

        $idEtudiant = (int) ($_SESSION['user_id'] ?? 0);
        $idOffre    = (int) ($_POST['id_offre'] ?? 0);

        if ($idOffre > 0) {
            $this->wishlistModel->remove($idEtudiant, $idOffre);
        }

        $this->redirect('/wishlist');
    }
}
