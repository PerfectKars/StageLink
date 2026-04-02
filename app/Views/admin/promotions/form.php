<?php
$errors    = $errors    ?? [];
$data      = $data      ?? [];
$edit      = $edit      ?? false;
$promotion = $promotion ?? [];
$formData  = $edit ? $promotion : $data;
$action    = $edit
    ? '/admin/promotions/' . (int)$promotion['Id_promotion'] . '/edit'
    : '/admin/promotions/create';
$titre     = $edit ? 'Modifier la promotion' : 'Créer une promotion';
$retour    = $edit ? '/admin/promotions/' . (int)$promotion['Id_promotion'] : '/admin/promotions';
?>
<main class="container" id="main-content">
    <a href="<?= $retour ?>" style="color:var(--text-muted);font-size:.9rem;">← Retour</a>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-top:1rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="form-card" style="margin-top:1.25rem;">
        <h1 class="form-title"><?= $titre ?></h1>
        <form method="POST" action="<?= $action ?>" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="form-group">
                <label class="form-label">Libellé <span>*</span></label>
                <input type="text" name="libelle" class="form-input" required
                       value="<?= htmlspecialchars($formData['libelle'] ?? $formData['Libelle'] ?? '') ?>"
                       placeholder="Ex : BTS SIO SLAM 2026">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Filière <span>*</span></label>
                    <input type="text" name="filiere" class="form-input" required
                           value="<?= htmlspecialchars($formData['filiere'] ?? $formData['Filiere'] ?? '') ?>"
                           placeholder="Ex : Informatique">
                </div>
                <div class="form-group">
                    <label class="form-label">Année <span>*</span></label>
                    <select name="annee" class="form-input" required>
                        <?php
                        $anneeActuelle = (int) date('Y');
                        $anneeVal = $formData['annee'] ?? $formData['Annee'] ?? '';
                        for ($a = $anneeActuelle - 1; $a <= $anneeActuelle + 3; $a++):
                            $label    = $a . '-' . ($a + 1);
                            $selected = $anneeVal === $label ? 'selected' : '';
                        ?>
                            <option value="<?= $label ?>" <?= $selected ?>><?= $label ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Pilote responsable</label>
                <select name="id_pilote" class="form-input">
                    <option value="">-- Aucun --</option>
                    <?php foreach ($pilotes as $pl): ?>
                        <option value="<?= (int)$pl['Id_pilote'] ?>"
                            <?= ((int)($formData['id_pilote'] ?? $formData['Id_pilote'] ?? 0) === (int)$pl['Id_pilote']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pl['prenom'] . ' ' . $pl['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <a href="<?= $retour ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Enregistrer' : 'Créer la promotion' ?></button>
            </div>
        </form>
    </section>
</main>
