<?php
$title = "Entreprises - StageLink";
$description = "Consultez la liste des entreprises proposant des stages.";
include '../templates/header.php';
?>

<h2>Entreprises</h2>
<div class="entreprises-list">
    <div class="entreprise">
        <h3>TechCorp</h3>
        <p>Email: contact@techcorp.com</p>
        <p>Téléphone: 01 23 45 67 89</p>
        <a href="/offres?entreprise=TechCorp" class="btn">Voir les offres</a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
