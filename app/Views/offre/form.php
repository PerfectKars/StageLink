<?php
/** @var array|null $offre */
/** @var array      $entreprises */
/** @var array      $competences */
/** @var array      $errors */
$isEdit      = isset($offre['Id_offre']);
$actionUrl   = $isEdit ? '/offres/' . $offre['Id_offre'] . '/edit' : '/offres/create';
$errors      = $errors ?? [];
$selectedIds = isset($offre['competences'])
    ? array_column($offre['competences'], 'Id_competence')
    : [];
?>

<main class="container" id="main-content">

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert" aria-live="assertive">
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Titre -->
            <div class="form-group">
                <label for="Titre" class="form-label">Titre <span aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="Titre"
                    name="Titre"
                    class="form-input"
                    value="<?= htmlspecialchars($offre['Titre'] ?? '') ?>"
                    required
                    maxlength="255"
                    placeholder="Ex : Développeur web PHP"
                    aria-required="true"
                >
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="Description" class="form-label">Description <span aria-hidden="true">*</span></label>
                <textarea
                    id="Description"
                    name="Description"
                    class="form-input"
                    rows="6"
                    required
                    placeholder="Décrivez les missions, le contexte, les attendus…"
                    aria-required="true"
                ><?= htmlspecialchars($offre['Description'] ?? '') ?></textarea>
            </div>

            <!-- Entreprise -->
            <div class="form-group">
                <label for="Id_entreprise" class="form-label">Entreprise <span aria-hidden="true">*</span></label>
                <select
                    id="Id_entreprise"
                    name="Id_entreprise"
                    class="form-input"
                    required
                    aria-required="true"
                >
                    <option value="">-- Sélectionner une entreprise --</option>
                    <?php foreach ($entreprises as $e): ?>
                        <option
                            value="<?= (int) $e['Id_entreprise'] ?>"
                            <?= ((int) ($offre['Id_entreprise'] ?? 0) === (int) $e['Id_entreprise']) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($e['Nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Gratification -->
            <div class="form-group">
                <label for="Base_remuneration" class="form-label">Gratification (€/h)</label>
                <input
                    type="number"
                    id="Base_remuneration"
                    name="Base_remuneration"
                    class="form-input"
                    value="<?= htmlspecialchars((string) ($offre['Base_remuneration'] ?? '')) ?>"
                    min="0"
                    step="0.01"
                    placeholder="Ex : 4.05"
                >
            </div>

            <!-- Date de l'offre -->
            <div class="form-group">
                <label for="Date_offre" class="form-label">Date de publication</label>
                <input
                    type="date"
                    id="Date_offre"
                    name="Date_offre"
                    class="form-input"
                    value="<?= htmlspecialchars($offre['Date_offre'] ?? date('Y-m-d')) ?>"
                >
            </div>

            <!-- Compétences -->
            <?php if (!empty($competences)): ?>
                <fieldset class="form-group">
                    <legend class="form-label">Compétences requises</legend>
                    <div class="competences-grid">
                        <?php foreach ($competences as $c): ?>
                            <label class="checkbox-label">
                                <input
                                    type="checkbox"
                                    name="competences[]"
                                    value="<?= (int) $c['Id_competence'] ?>"
                                    <?= in_array((int) $c['Id_competence'], $selectedIds, true) ? 'checked' : '' ?>
                                >
                                <?= htmlspecialchars($c['Nom_competence']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>

            <!-- Actions -->
            <div class="form-actions">
                <a href="/offres" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'offre' ?>
                </button>
            </div>
        </form>
    </section>

</main>
