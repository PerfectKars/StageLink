<section class="section">
    <div class="container container--sm">
        <a href="/entreprises" style="color:var(--text-muted);font-size:.9rem;">← Retour aux entreprises</a>

        <h1 style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;margin:1rem 0 1.5rem;">
            <?= $entreprise ? 'Modifier l\'entreprise' : 'Créer une entreprise' ?>
        </h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="<?= $entreprise ? '/entreprises/'.$entreprise['Id_entreprise'].'/edit' : '/entreprises/create' ?>"
              class="form card" style="padding:1.5rem;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form__group">
                <label for="Nom">Nom de l'entreprise *</label>
                <input type="text" id="Nom" name="Nom" required
                       value="<?= htmlspecialchars($entreprise['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form__group">
                <label for="Description">Description</label>
                <textarea id="Description" name="Description" rows="4"><?= htmlspecialchars($entreprise['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form__group">
                <label for="Email_contact">Email de contact</label>
                <input type="email" id="Email_contact" name="Email_contact"
                       value="<?= htmlspecialchars($entreprise['Email_contact'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form__group">
                <label for="Tel_contact">Téléphone</label>
                <input type="tel" id="Tel_contact" name="Tel_contact"
                       value="<?= htmlspecialchars($entreprise['Tel_contact'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div style="display:flex;gap:1rem;margin-top:.5rem;">
                <button type="submit" class="btn btn--primary btn--full">
                    <?= $entreprise ? 'Sauvegarder' : 'Créer' ?>
                </button>
                <a href="/entreprises" class="btn btn--outline btn--full">Annuler</a>
            </div>

            <?php if ($entreprise): ?>
            <form method="POST" action="/entreprises/<?= $entreprise['Id_entreprise'] ?>/delete"
                  onsubmit="return confirm('Supprimer cette entreprise ?')"
                  style="margin-top:1rem;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit" class="btn btn--full" style="background:#FEE2E2;color:#DC2626;">
                    Supprimer l'entreprise
                </button>
            </form>
            <?php endif; ?>
        </form>
    </div>
</section>
