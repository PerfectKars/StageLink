<?php
/** @var array|null $offre */
/** @var array      $entreprises */
/** @var array      $sitesParEntreprise */
/** @var array      $competences */
/** @var array      $errors */
$isEdit      = isset($offre['Id_offre']);
$actionUrl   = $isEdit ? '/offres/' . $offre['Id_offre'] . '/edit' : '/offres/create';
$errors      = $errors ?? [];
$selectedIds = isset($offre['competences']) ? array_column($offre['competences'], 'Id_competence') : [];
// Après
$sitesJson = json_encode($sitesParEntreprise ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
?>
<main class="container" id="main-content">

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="form-card">
        <h1 class="form-title">
            <?= $isEdit ? 'Modifier l\'offre' : 'Créer une offre de stage' ?>
        </h1>

        <form method="POST" action="<?= htmlspecialchars($actionUrl) ?>" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Titre -->
            <div class="form-group">
                <label for="Titre" class="form-label">Titre <span>*</span></label>
                <input type="text" id="Titre" name="Titre" class="form-input"
                       value="<?= htmlspecialchars($offre['Titre'] ?? '') ?>"
                       required maxlength="200" placeholder="Ex : Développeur web PHP">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="Description" class="form-label">Description <span>*</span></label>
                <textarea id="Description" name="Description" class="form-input" rows="6" required
                          placeholder="Décrivez les missions, le contexte, les attendus…"
                ><?= htmlspecialchars($offre['Description'] ?? '') ?></textarea>
            </div>

            <!-- Entreprise -->
            <div class="form-group">
                <label for="Id_entreprise" class="form-label">Entreprise <span>*</span></label>
                <select id="Id_entreprise" name="Id_entreprise" class="form-input" required>
                    <option value="">-- Sélectionner une entreprise --</option>
                    <?php foreach ($entreprises as $e): ?>
                        <option value="<?= (int)$e['Id_entreprise'] ?>"
                            <?= ((int)($offre['Id_entreprise'] ?? 0) === (int)$e['Id_entreprise']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['Nom']) ?>
                            <?php if (!empty($e['SIRET'])): ?>
                                (SIRET : <?= htmlspecialchars($e['SIRET']) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Site / lieu d'exercice -->
            <div class="form-group">
                <label for="Id_site" class="form-label">
                    Lieu d'exercice <span>*</span>
                    <small style="font-weight:normal;color:var(--text-muted);">— adresse officielle du poste</small>
                </label>
                <select id="Id_site" name="Id_site" class="form-input" required>
                    <option value="">-- Sélectionner d'abord une entreprise --</option>
                </select>
                <p id="site-hint" style="display:none;font-size:.85rem;color:var(--text-muted);margin-top:.25rem;">
                    ℹ️ Le lieu d'exercice est l'adresse juridique du poste.
                </p>
            </div>

            <!-- Durée -->
            <div class="form-group">
                <label for="duree_mois" class="form-label">Durée du stage (mois) <span>*</span></label>
                <input type="number" id="duree_mois" name="duree_mois" class="form-input"
                       value="<?= (int)($offre['duree_mois'] ?? '') ?>"
                       min="1" max="24" required placeholder="Ex : 3">
            </div>

            <!-- Gratification -->
            <div class="form-group">
                <label for="Base_remuneration" class="form-label">Gratification (€/h)</label>
                <input type="number" id="Base_remuneration" name="Base_remuneration" class="form-input"
                       value="<?= htmlspecialchars((string)($offre['Base_remuneration'] ?? '')) ?>"
                       min="0" step="0.01" placeholder="Ex : 4.50">
                <small style="color:var(--text-muted);">Minimum légal 2026 : 4,50 €/h</small>
            </div>

            <!-- Date de publication -->
            <div class="form-group">
                <label for="Date_offre" class="form-label">Date de publication</label>
                <input type="date" id="Date_offre" name="Date_offre" class="form-input"
                       value="<?= htmlspecialchars($offre['Date_offre'] ?? date('Y-m-d')) ?>">
            </div>

            <!-- Compétences -->
            <?php if (!empty($competences)): ?>
            <fieldset class="form-group">
                <legend class="form-label">Compétences requises</legend>
                <div class="competences-grid">
                    <?php foreach ($competences as $c): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="competences[]"
                                   value="<?= (int)$c['Id_competence'] ?>"
                                <?= in_array((int)$c['Id_competence'], $selectedIds, true) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($c['Nom_competence']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endif; ?>

            <div class="form-actions">
                <a href="/offres" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer' : 'Créer l\'offre' ?>
                </button>
            </div>
        </form>
    </section>

    <script>
    window.offreFormData = {
        sitesParEntreprise: <?= $sitesJson ?? '{}' ?>,
        currentSite: <?= (int)($offre['Id_site'] ?? 0) ?>
    };
</script>

</main>

