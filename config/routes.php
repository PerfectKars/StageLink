<?php
declare(strict_types=1);

use App\Core\Router;

$router = new Router();

// ── Publiques ─────────────────────────────────────────────────────────────────
$router->get('/',                          ['HomeController',        'index']);
$router->get('/mentions-legales',          ['HomeController',        'mentionsLegales']);

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
$router->post('/offres/:id/postuler',      ['CandidatureController', 'postuler']);
$router->get('/mes-candidatures',          ['CandidatureController', 'mesCandidatures']);

// ── Wishlist ──────────────────────────────────────────────────────────────────
$router->get('/wishlist',                  ['WishlistController',    'index']);
$router->post('/wishlist/:id/add',         ['WishlistController',    'add']);
$router->post('/wishlist/:id/remove',      ['WishlistController',    'remove']);

// ── Profil ────────────────────────────────────────────────────────────────────
$router->get('/profil',                    ['ProfilController',      'index']);
$router->post('/profil',                   ['ProfilController',      'update']);

// ── Admin : Étudiants ─────────────────────────────────────────────────────────
$router->get('/admin/etudiants',           ['EtudiantController',    'index']);
$router->get('/admin/etudiants/create',    ['EtudiantController',    'createForm']);
$router->post('/admin/etudiants/create',   ['EtudiantController',    'create']);
$router->get('/admin/etudiants/:id',       ['EtudiantController',    'show']);
$router->get('/admin/etudiants/:id/edit',  ['EtudiantController',    'editForm']);
$router->post('/admin/etudiants/:id/edit', ['EtudiantController',    'edit']);
$router->post('/admin/etudiants/:id/delete',['EtudiantController',   'delete']);

// ── Admin : Pilotes ───────────────────────────────────────────────────────────
$router->get('/admin/pilotes',             ['PiloteController',      'index']);
$router->get('/admin/pilotes/create',      ['PiloteController',      'createForm']);
$router->post('/admin/pilotes/create',     ['PiloteController',      'create']);
$router->get('/admin/pilotes/:id',         ['PiloteController',      'show']);
$router->get('/admin/pilotes/:id/edit',    ['PiloteController',      'editForm']);
$router->post('/admin/pilotes/:id/edit',   ['PiloteController',      'edit']);
$router->post('/admin/pilotes/:id/delete', ['PiloteController',      'delete']);

return $router;
