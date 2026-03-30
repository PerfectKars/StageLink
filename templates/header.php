<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StageLink — Plateforme de recherche de stages pour étudiants CESI">
    <meta name="keywords" content="stage, offre de stage, alternance, CESI, recrutement">
    <title><?= htmlspecialchars($title ?? 'StageLink', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <nav class="navbar" aria-label="Navigation principale">
        <a href="/" class="navbar__brand">
            <img src="/assets/img/Logo.webp" alt="StageLink" style="height:60px;width:auto;">
            STAGELINK
        </a>
        <button class="navbar__burger" aria-label="Menu" aria-expanded="false" aria-controls="nav-menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="navbar__menu" id="nav-menu" role="list">
            <li><a href="/offres">Offres</a></li>
            <li><a href="/entreprises">Entreprises</a></li>

            <?php if (!empty($_SESSION['user'])): ?>
                <?php $role = $_SESSION['user']['role'] ?? ''; ?>

                <?php if ($role === 'etudiant'): ?>
                    <li><a href="/wishlist">Ma wishlist</a></li>
                    <li><a href="/mes-candidatures">Mes candidatures</a></li>
                <?php endif; ?>

                <?php if (in_array($role, ['admin', 'pilote'])): ?>
                    <li><a href="/offres/statistiques">Statistiques</a></li>
                <?php endif; ?>

                <?php if ($role === 'pilote'): ?>
                    <li><a href="/pilote/promotions">Mes promotions</a></li>
                    <li><a href="/pilote/candidatures">Candidatures</a></li>
                <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                    <li><a href="/admin/utilisateurs">Utilisateurs</a></li>
                    <li><a href="/admin/promotions/create">Créer promotion</a></li>
                <?php endif; ?>

                <!-- Avatar + lien profil -->
                <li>
                    <a href="/profil" style="display:flex;align-items:center;gap:.4rem;">
                        <?php $photo = $_SESSION['user']['photo'] ?? null; ?>
                        <?php if ($photo): ?>
                            <img src="/uploads/photos/<?= htmlspecialchars($photo) ?>"
                                 alt="Photo de profil"
                                 style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <span style="width:28px;height:28px;background:var(--primary);
                                         border-radius:50%;display:flex;align-items:center;
                                         justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                                <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'U', 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Profil', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
                <li><a href="/logout" class="btn btn--outline">Déconnexion</a></li>

            <?php else: ?>
                <li><a href="/login" class="btn btn--primary">Se connecter</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="main-content">