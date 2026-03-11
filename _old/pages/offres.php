<?php
$title = "Offres de stage - StageLink";
$description = "Consultez toutes les offres de stage disponibles sur StageLink.";
include '../templates/header.php';
?>

<h2>Liste des offres de stage</h2>
<div class="offres-list">
    <!-- Exemple d'offre -->
    <div class="offre">
        <h3>Développeur PHP Junior</h3>
        <p>Entreprise: TechCorp</p>
        <p>Compétences: PHP, MySQL, HTML/CSS</p>
        <a href="/offre?id=1" class="btn">Voir l'offre</a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
