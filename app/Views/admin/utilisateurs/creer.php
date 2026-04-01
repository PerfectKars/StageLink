<?php
/** @var string $type */
/** @var array  $promotions */
/** @var array  $errors */
/** @var array  $data */
$errors = $errors ?? [];
$data   = $data   ?? [];
$label  = $type === 'pilote' ? 'un pilote' : 'un étudiant';
?>
<main class="container" id="main-content">

    <a href="/admin/utilisateurs" style="color:var(--text-muted);font-size:.9rem;">
        ← Gestion des utilisateurs
    </a>

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
        <h1 class="form-title">Créer <?= $label ?></h1>

        <form method="POST" action="/admin/utilisateurs/creer" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Nom <span>*</span></label>
                    <input type="text" name="nom" class="form-input" required
                           value="<?= htmlspecialchars($data['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom <span>*</span></label>
                    <input type="text" name="prenom" class="form-input" required
                           value="<?= htmlspecialchars($data['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email <span>*</span></label>
                <input type="email" name="email" class="form-input" required
                       value="<?= htmlspecialchars($data['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-input" maxlength="10"
                       value="<?= htmlspecialchars($data['telephone'] ?? '') ?>"
                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe <span>*</span>
                    <small style="font-weight:normal;color:var(--text-muted);">— min. 8 caractères</small>
                </label>
                <input type="password" name="mot_de_passe" class="form-input" required minlength="8">
            </div>

            <?php if ($type === 'etudiant'): ?>
            <div class="form-group">
                <label class="form-label">Promotion <span>*</span></label>
                <select name="id_promotion" class="form-input" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($promotions as $p): ?>
                        <option value="<?= (int)$p['Id_promotion'] ?>"
                            <?= ((int)($data['id_promotion'] ?? 0) === (int)$p['Id_promotion']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['Libelle']) ?> (<?= (int)$p['Annee'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Statut de recherche</label>
                <select name="statut_recherche" class="form-input">
                    <?php foreach (['En recherche', 'Stage trouvé', 'Non disponible'] as $s): ?>
                        <option value="<?= $s ?>"
                            <?= ($data['statut_recherche'] ?? 'En recherche') === $s ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="/admin/utilisateurs" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    Créer <?= $label ?>
                </button>
            </div>
        </form>
    </section>
</main>
