<?php $errors = $errors ?? []; $data = $data ?? []; ?>
<main class="container" id="main-content">
    <a href="/admin/utilisateurs" style="color:var(--text-muted);font-size:.9rem;">← Utilisateurs</a>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-top:1rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="form-card" style="margin-top:1.25rem;">
        <h1 class="form-title">Créer une promotion</h1>
        <form method="POST" action="/admin/promotions/create" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label class="form-label">Libellé <span>*</span></label>
                <input type="text" name="libelle" class="form-input" required
                       value="<?= htmlspecialchars($data['libelle'] ?? '') ?>"
                       placeholder="Ex : BTS SIO SLAM 2026">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Filière <span>*</span></label>
                    <input type="text" name="filiere" class="form-input" required
                           value="<?= htmlspecialchars($data['filiere'] ?? '') ?>"
                           placeholder="Ex : Informatique">
                </div>
                <div class="form-group">
                    <label class="form-label">Année <span>*</span></label>
                    <select name="annee" class="form-input" required>
    <?php
    $anneeActuelle = (int) date('Y');
    for ($a = $anneeActuelle - 1; $a <= $anneeActuelle + 3; $a++):
        $label    = $a . '-' . ($a + 1);
        $selected = ($data['annee'] ?? '') === $label ? 'selected' : '';
    ?>
        <option value="<?= $label ?>" <?= $selected ?>>
            <?= $label ?>
        </option>
    <?php endfor; ?>
</select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilote responsable</label>
                <select name="id_pilote" class="form-input">
                    <option value="">-- Aucun --</option>
                    <?php foreach ($pilotes as $p): ?>
                        <option value="<?= (int)$p['Id_pilote'] ?>"
                            <?= ((int)($data['id_pilote'] ?? 0) === (int)$p['Id_pilote']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="/admin/utilisateurs" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Créer la promotion</button>
            </div>
        </form>
    </section>
</main>
