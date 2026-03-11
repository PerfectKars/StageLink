<?php
$title = "Accueil - StageLink";
$description = "Trouvez facilement des stages correspondant à votre profil étudiant.";
include '../templates/header.php';
?>

<section class="hero">
    <h2>Bienvenue sur StageLink</h2>
    <p>Recherchez, postulez et gérez vos stages facilement.</p>
    <a href="/offres" class="btn">Voir les offres</a>
</section>

<section class="features">
    <div class="feature">
        <h3>Entreprises</h3>
        <p>Accédez aux entreprises qui recrutent des stagiaires.</p>
    </div>
    <div class="feature">
        <h3>Offres de stage</h3>
        <p>Filtrez les offres selon vos compétences et préférences.</p>
    </div>
    <div class="feature">
        <h3>Profil étudiant</h3>
        <p>Gérez vos candidatures et votre wish-list facilement.</p>
    </div>
</section>

<?php include '../templates/footer.php'; ?>
