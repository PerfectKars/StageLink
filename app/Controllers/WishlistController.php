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

    /** GET /wishlist */
    private const PER_PAGE = 5;

public function index(): void
{
    $this->requireRole('etudiant');
    $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
    $page          = max(1, (int) ($_GET['page'] ?? 1));
    $offres        = $this->wishlistModel->findByEtudiant($idUtilisateur, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
    $total         = $this->wishlistModel->countByEtudiant($idUtilisateur);
    $this->render('wishlist/index', [
        'title'   => 'Ma liste de souhaits',
        'offres'  => $offres,
        'page'    => $page,
        'perPage' => self::PER_PAGE,
        'total'   => $total,
    ]);
}

    /** POST /wishlist/add */
    public function add(): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();

        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $idOffre       = (int) ($_POST['id_offre'] ?? 0);
        $redirect      = $_POST['redirect'] ?? '/wishlist';

        if ($idOffre > 0) {
            $this->wishlistModel->add($idUtilisateur, $idOffre);
            $_SESSION['flash_success'] = 'Offre ajoutée à votre wishlist. ❤️';
        }

        $this->redirect($redirect);
    }

    /** POST /wishlist/remove */
    public function remove(): void
    {
        $this->requireRole('etudiant');
        $this->verifyCsrf();

        $idUtilisateur = (int) ($_SESSION['user']['id'] ?? 0);
        $idOffre       = (int) ($_POST['id_offre'] ?? 0);
        $redirect      = $_POST['redirect'] ?? '/wishlist';

        if ($idOffre > 0) {
            $this->wishlistModel->remove($idUtilisateur, $idOffre);
            $_SESSION['flash_success'] = 'Offre retirée de votre wishlist.';
        }

        $this->redirect($redirect);
    }
}
