<?php
/** @var array|null $etudiant */
/** @var array      $promotions */
/** @var array      $errors */
$isEdit    = isset($etudiant['Id_etudiant']);
$actionUrl = $isEdit
    ? '/admin/etudiants/' . $etudiant['Id_etudiant'] . '/edit'
    : '/admin/etudiants/create';
$errors    = $errors ?? [];
$statuts   = ['En recherche', 'Stage trouvé', 'Non disponible'];
?>
<main class="container" id="main-content">

    <a href="/admin/etudiants" style="color:var(--text-muted);font-size:.9rem;">← Liste des étudiants</a>

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
            <?= $isEdit ? 'Modifier l\'étudiant' : 'Créer un étudiant' ?>
        </h1>

        <form method="POST" action="<?= htmlspecialchars($actionUrl) ?>" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Nom <span>*</span></label>
                    <input type="text" name="nom" class="form-input" required
                           value="<?= htmlspecialchars($etudiant['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom <span>*</span></label>
                    <input type="text" name="prenom" class="form-input" required
                           value="<?= htmlspecialchars($etudiant['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email <span>*</span></label>
                <input type="email" name="email" class="form-input" required
                       value="<?= htmlspecialchars($etudiant['Email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-input" maxlength="10"
                       value="<?= htmlspecialchars($etudiant['Telephone'] ?? '') ?>"
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

            <div class="form-group">
                <label class="form-label">Promotion</label>
                <select name="id_promotion" class="form-input">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($promotions as $p): ?>
                        <option value="<?= (int)$p['Id_promotion'] ?>"
                            <?= ((int)($etudiant['id_promotion'] ?? 0) === (int)$p['Id_promotion']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['Libelle']) ?> (<?= htmlspecialchars($p['Annee'] ?? '') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Statut de recherche</label>
                <select name="statut_recherche" class="form-input">
                    <?php foreach ($statuts as $s): ?>
                        <option value="<?= $s ?>"
                            <?= ($etudiant['Statut_recherche'] ?? 'En recherche') === $s ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="/admin/etudiants" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer' : 'Créer l\'étudiant' ?>
                </button>
            </div>
        </form>
    </section>
</main>
