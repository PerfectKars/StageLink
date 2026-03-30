<?php
/** @var array|null $pilote */
/** @var array      $errors */
$isEdit    = isset($pilote['Id_pilote']);
$actionUrl = $isEdit
    ? '/admin/pilotes/' . $pilote['Id_pilote'] . '/edit'
    : '/admin/pilotes/create';
$errors    = $errors ?? [];
?>
<main class="container" id="main-content">

    <a href="/admin/pilotes" style="color:var(--text-muted);font-size:.9rem;">← Liste des pilotes</a>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-top:1rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="form-card" style="margin-top:1.25rem;">
        <h1 class="form-title">
            <?= $isEdit ? 'Modifier le pilote' : 'Créer un pilote' ?>
        </h1>

        <form method="POST" action="<?= htmlspecialchars($actionUrl) ?>" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Nom <span>*</span></label>
                    <input type="text" name="nom" class="form-input" required
                           value="<?= htmlspecialchars($pilote['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom <span>*</span></label>
                    <input type="text" name="prenom" class="form-input" required
                           value="<?= htmlspecialchars($pilote['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email <span>*</span></label>
                <input type="email" name="email" class="form-input" required
                       value="<?= htmlspecialchars($pilote['Email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-input" maxlength="10"
                       value="<?= htmlspecialchars($pilote['Telephone'] ?? '') ?>"
                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
            </div>

            <div class="form-group">
                <label class="form-label">
                    <?= $isEdit ? 'Nouveau mot de passe' : 'Mot de passe' ?>
                    <?php if (!$isEdit): ?><span>*</span><?php endif; ?>
                    <small style="font-weight:normal;color:var(--text-muted);">
                        <?= $isEdit ? '— laisser vide pour ne pas changer' : '— min. 8 caractères' ?>
                    </small>
                </label>
                <input type="password" name="mot_de_passe" class="form-input"
                       <?= !$isEdit ? 'required minlength="8"' : '' ?>>
            </div>

            <div class="form-actions">
                <a href="/admin/pilotes" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer' : 'Créer le pilote' ?>
                </button>
            </div>
        </form>
    </section>
</main>
