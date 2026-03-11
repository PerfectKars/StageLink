<section class="auth-section">
    <div class="container container--sm">
        <h1>Connexion</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form__group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form__group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn--primary btn--full">Se connecter</button>
        </form>
    </div>
</section>
