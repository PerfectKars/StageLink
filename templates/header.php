<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StageLink — Plateforme de recherche de stages pour étudiants CESI">
    <meta name="keywords" content="stage, offre de stage, alternance, CESI, recrutement">
    <title><?= htmlspecialchars($title ?? 'StageLink', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar" aria-label="Navigation principale">
        <a href="/" class="navbar__brand">
            <strong>Stage<span>Link</span></strong>
        </a>

        <button class="navbar__burger" aria-label="Menu" aria-expanded="false" aria-controls="nav-menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="navbar__menu" id="nav-menu" role="list">
            <li><a href="/offres">Offres</a></li>
            <li><a href="/entreprises">Entreprises</a></li>

            <?php if (!empty($_SESSION['user'])): ?>
                <li><a href="/wishlist">Ma wishlist</a></li>

                <?php if (in_array($_SESSION['user']['role'], ['admin', 'pilote'])): ?>
                    <li><a href="/offres/statistiques">Statistiques</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="/admin/etudiants">Étudiants</a></li>
                    <li><a href="/admin/pilotes">Pilotes</a></li>
                <?php endif; ?>

                <li><a href="/profil"><?= htmlspecialchars($_SESSION['user']['prenom'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <li><a href="/logout" class="btn btn--outline">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="/login" class="btn btn--primary">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="main-content">
