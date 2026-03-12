<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "StageLink - Recherche de stages" ?></title>
    <meta name="description" content="<?= $description ?? "Plateforme pour rechercher et gérer des offres de stages pour étudiants et entreprises." ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/img/favicon.png" type="image/png">
</head>
<body>
<header>
    <div class="container">
        <h1><a href="/home">StageLink</a></h1>
        <nav>
            <ul class="menu">
                <li><a href="/home">Accueil</a></li>
                <li><a href="/offres">Offres de stage</a></li>
                <li><a href="/entreprises">Entreprises</a></li>
                <li><a href="/profil">Profil</a></li>
                <li><a href="/login">Connexion</a></li>
            </ul>
            <div class="burger">
                <span></span><span></span><span></span>
            </div>
        </nav>
    </div>
</header>
<main>
