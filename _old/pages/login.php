<?php
$title = "Connexion - StageLink";
include '../templates/header.php';
?>

<h2>Connexion</h2>
<form action="/login" method="POST">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    
    <label for="password">Mot de passe:</label>
    <input type="password" id="password" name="password" required>
    
    <button type="submit">Se connecter</button>
</form>

<?php include '../templates/footer.php'; ?>
