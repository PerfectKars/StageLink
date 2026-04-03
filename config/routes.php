<?php
declare(strict_types=1);

use App\Core\Router;

$router = new Router();

// ── Publiques ─────────────────────────────────────────────────────────────────
$router->get('/',                          ['HomeController', 'index']);
$router->get('/mentions-legales',          ['HomeController', 'mentionsLegales']);
$router->get('/cookies',                    ['HomeController', 'cookies']);
$router->get('/nous_contacter',            ['HomeController', 'nousContacter']);

// ── Authentification ──────────────────────────────────────────────────────────
$router->get('/login',                     ['AuthController',        'loginForm']);
$router->post('/login',                    ['AuthController',        'login']);
$router->get('/logout',                    ['AuthController',        'logout']);

// ── Offres ────────────────────────────────────────────────────────────────────
$router->get('/offres',                    ['OffreController',       'index']);
$router->get('/offres/statistiques',       ['OffreController',       'statistiques']);
$router->get('/offres/create',             ['OffreController',       'createForm']);
$router->post('/offres/create',            ['OffreController',       'create']);
$router->get('/offres/:id',                ['OffreController',       'show']);
$router->get('/offres/:id/edit',           ['OffreController',       'editForm']);
$router->post('/offres/:id/edit',          ['OffreController',       'edit']);
$router->post('/offres/:id/delete',        ['OffreController',       'delete']);
$router->post('/offres/:id/statut',        ['OffreController', 'toggleStatut']);

// ── Entreprises ───────────────────────────────────────────────────────────────
$router->get('/entreprises',               ['EntrepriseController',  'index']);
$router->get('/entreprises/create',        ['EntrepriseController',  'createForm']);
$router->post('/entreprises/create',       ['EntrepriseController',  'create']);
$router->get('/entreprises/:id',           ['EntrepriseController',  'show']);
$router->get('/entreprises/:id/edit',      ['EntrepriseController',  'editForm']);
$router->post('/entreprises/:id/edit',     ['EntrepriseController',  'edit']);
$router->post('/entreprises/:id/delete',   ['EntrepriseController',  'delete']);
$router->post('/entreprises/:id/noter',    ['EntrepriseController',  'noter']);

// ── Candidatures ──────────────────────────────────────────────────────────────
$router->get('/offres/:id/postuler',  ['CandidatureController', 'postulerForm']);
$router->post('/offres/:id/postuler', ['CandidatureController', 'postuler']);
$router->get('/mes-candidatures',          ['CandidatureController', 'mesCandidatures']);
$router->get('/pilote/candidatures',       ['CandidatureController', 'candidaturesPromotion']);
$router->get('/pilote/candidatures/:id',   ['CandidatureController', 'detailCandidature']);
$router->get('/pilote/promotions',       ['PiloteController', 'promotions']);
$router->get('/pilote/promotions/:id',   ['PiloteController', 'promotion']);
$router->get('/pilote/etudiants/:id',    ['PiloteController', 'etudiant']);
$router->post('/candidatures/:idOffre/:idEtudiant/statut', ['CandidatureController', 'updateStatut']);
$router->get('/cv/:idCv', ['CandidatureController', 'telechargerCv']);
$router->post('/mes-candidatures/:idOffre/confirmer', ['CandidatureController', 'confirmerStage']);

// ── Wishlist ──────────────────────────────────────────────────────────────────
$router->get('/wishlist',        ['WishlistController', 'index']);
$router->post('/wishlist/add',   ['WishlistController', 'add']);
$router->post('/wishlist/remove',['WishlistController', 'remove']);

// ── Profil ────────────────────────────────────────────────────────────────────
$router->get('/profil',                    ['ProfilController',      'index']);
$router->post('/profil',                   ['ProfilController',      'update']);
$router->get('/uploads/photos/:fichier', ['ProfilController', 'servirPhoto']);

// ── Pilotes ──────────────────────────────────────────────────────
$router->get('/pilote/etudiants/create',  ['EtudiantController', 'createForm']);
$router->post('/pilote/etudiants/create', ['EtudiantController', 'create']);

// ── Admin : Utilisateurs ──────────────────────────────────────────────────────
$router->get('/admin/utilisateurs',          ['AdminController', 'utilisateurs']);
$router->get('/admin/utilisateurs/creer',    ['AdminController', 'creerForm']);
$router->post('/admin/utilisateurs/creer',   ['AdminController', 'creer']);

// ── Admin : Étudiants ─────────────────────────────────────────────────────────
$router->get('/admin/etudiants',             ['EtudiantController', 'index']);
$router->get('/admin/etudiants/create',      ['EtudiantController', 'createForm']);
$router->post('/admin/etudiants/create',     ['EtudiantController', 'create']);
$router->get('/admin/etudiants/:id',         ['EtudiantController', 'show']);
$router->get('/admin/etudiants/:id/edit',    ['EtudiantController', 'editForm']);
$router->post('/admin/etudiants/:id/edit',   ['EtudiantController', 'edit']);
$router->post('/admin/etudiants/:id/delete', ['EtudiantController', 'delete']);

// ── Admin : Pilotes ───────────────────────────────────────────────────────────
$router->get('/admin/pilotes',             ['PiloteController',      'index']);
$router->get('/admin/pilotes/create',      ['PiloteController',      'createForm']);
$router->post('/admin/pilotes/create',     ['PiloteController',      'create']);
$router->get('/admin/pilotes/:id',         ['PiloteController',      'show']);
$router->get('/admin/pilotes/:id/edit',    ['PiloteController',      'editForm']);
$router->post('/admin/pilotes/:id/edit',   ['PiloteController',      'edit']);
$router->post('/admin/pilotes/:id/delete', ['PiloteController',      'delete']);

// ── Admin : Promotions ───────────────────────────────────────────────────────────
$router->get('/admin/promotions/create',  ['AdminController', 'promotionForm']);
$router->post('/admin/promotions/create', ['AdminController', 'promotionCreate']);
$router->get('/admin/promotions/:id', ['AdminController', 'promotionDetail']);


return $router;
