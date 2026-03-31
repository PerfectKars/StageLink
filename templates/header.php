<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Meta de base SEO -->
    <meta name="description" content="<?= htmlspecialchars($description ?? 'StageLink — Plateforme de recherche de stages pour étudiants CESI. Découvrez les meilleures offres de stages en France.', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="stage, offre de stage, alternance, CESI, recrutement, recherche stage, stagiaire">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    <meta name="author" content="StageLink - CESI Nancy">
    <meta name="copyright" content="© 2025 StageLink. Tous droits réservés.">
    <meta name="theme-color" content="#1a73e8">
    
    <!-- Open Graph (réseaux sociaux) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? ($title ?? 'StageLink'), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? ($description ?? 'Plateforme de recherche de stages pour étudiants CESI'), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="https://stagelink.com<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="StageLink">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? 'https://stagelink.com/assets/img/og-image.jpg', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle ?? ($title ?? 'StageLink'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription ?? ($description ?? 'Plateforme de recherche de stages pour étudiants CESI'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage ?? 'https://stagelink.com/assets/img/og-image.jpg', ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Canonical (évite duplicate content) -->
    <link rel="canonical" href="https://stagelink.com<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <title><?= htmlspecialchars($title ?? 'StageLink — Plateforme de recherche de stages', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- JSON-LD Structured Data (Schema.org) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "StageLink",
        "url": "https://stagelink.com",
        "description": "Plateforme de recherche de stages pour étudiants CESI",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "https://stagelink.com/offres?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        },
        "sameAs": [
            "https://www.linkedin.com/company/cesi",
            "https://www.facebook.com/cesi"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+33-9-XX-XX-XX-XX",
            "contactType": "Customer Service",
            "email": "contact@stagelink.fr"
        }
    }
    </script>

    <!-- JSON-LD Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "StageLink",
        "url": "https://stagelink.com",
        "logo": "https://stagelink.com/assets/img/Logo.webp",
        "description": "Plateforme de recherche de stages pour étudiants",
        "email": "contact@stagelink.fr",
        "foundingDate": "2025"
    }
    </script>
</head>
<body>
<header class="site-header">
    <nav class="navbar" aria-label="Navigation principale">
        <a href="/" class="navbar__brand">
            <img src="/assets/img/Logo.webp" alt="StageLink — Plateforme de recherche de stages" style="height:60px;width:auto;">
            STAGELINK
        </a>
        <button class="navbar__burger" aria-label="Menu" aria-expanded="false" aria-controls="nav-menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="navbar__menu" id="nav-menu" role="list">
            <li><a href="/offres" title="Consulter toutes les offres de stage">Offres</a></li>
            <li><a href="/entreprises" title="Parcourir les entreprises partenaires">Entreprises</a></li>

            <?php if (!empty($_SESSION['user'])): ?>
                <?php $role = $_SESSION['user']['role'] ?? ''; ?>

                <?php if ($role === 'etudiant'): ?>
                    <li><a href="/wishlist" title="Consulter ma liste de favoris">Ma wishlist</a></li>
                    <li><a href="/mes-candidatures" title="Gérer mes candidatures">Mes candidatures</a></li>
                <?php endif; ?>

                <?php if (in_array($role, ['admin', 'pilote'])): ?>
                    <li><a href="/offres/statistiques" title="Voir les statistiques des offres">Statistiques</a></li>
                <?php endif; ?>

                <?php if ($role === 'pilote'): ?>
                    <li><a href="/pilote/promotions" title="Gérer mes promotions">Mes promotions</a></li>
                    <li><a href="/pilote/candidatures" title="Consulter les candidatures">Candidatures</a></li>
                <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                    <li><a href="/admin/utilisateurs" title="Gérer les utilisateurs">Utilisateurs</a></li>
                    <li><a href="/admin/promotions/create" title="Créer une nouvelle promotion">Créer promotion</a></li>
                <?php endif; ?>

                <!-- Avatar + lien profil -->
                <li>
                    <a href="/profil" title="Accéder à mon profil" style="display:flex;align-items:center;gap:.4rem;">
                        <?php $photo = $_SESSION['user']['photo'] ?? null; ?>
                        <?php if ($photo): ?>
                            <img src="/uploads/photos/<?= htmlspecialchars($photo) ?>"
                                 alt="Photo de profil de <?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'utilisateur', ENT_QUOTES, 'UTF-8') ?>"
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
                <li><a href="/logout" class="btn btn--outline" title="Se déconnecter">Déconnexion</a></li>

            <?php else: ?>
                <li><a href="/login" class="btn btn--primary" title="Se connecter à StageLink">Se connecter</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="main-content" role="main">
