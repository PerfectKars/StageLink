<?php
/** @var array      $offre */
/** @var array      $cvExistants */
/** @var array      $errors */
/** @var string     $lettre */
$errors      = $errors ?? [];
$lettre      = $lettre ?? '';
$cvExistants = $cvExistants ?? [];
?>
<main class="container" id="main-content">

    <!-- Breadcrumb -->
    <nav style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.5rem;">
        <a href="/offres">Offres</a> /
        <a href="/offres/<?= (int)$offre['Id_offre'] ?>">
            <?= htmlspecialchars($offre['Titre'] ?? '') ?>
        </a> /
        <span>Postuler</span>
    </nav>

    <!-- Résumé de l'offre -->
    <div class="card" style="margin-bottom:2rem;padding:1.25rem 1.5rem;">
        <h2 style="font-size:1.1rem;margin-bottom:.5rem;">
            <?= htmlspecialchars($offre['Titre'] ?? '') ?>
        </h2>
        <p class="card__meta">
            🏢 <a href="/entreprises/<?= (int)$offre['Id_entreprise'] ?>">
                <?= htmlspecialchars($offre['Nom_entreprise'] ?? '') ?>
            </a>
            <?php if (!empty($offre['Ville'])): ?>
                &nbsp;|&nbsp; 📍 <?= htmlspecialchars($offre['Ville']) ?>
            <?php endif; ?>
            <?php if (!empty($offre['duree_mois'])): ?>
                &nbsp;|&nbsp; 🗓 <?= (int)$offre['duree_mois'] ?> mois
            <?php endif; ?>
            <?php if (!empty($offre['Base_remuneration'])): ?>
                &nbsp;|&nbsp; 💶 <?= number_format((float)$offre['Base_remuneration'], 2) ?> €/h
            <?php endif; ?>
        </p>
    </div>

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert" style="margin-bottom:1.5rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="form-card">
        <h1 class="form-title">Déposer ma candidature</h1>

        <form method="POST"
              action="/offres/<?= (int)$offre['Id_offre'] ?>/postuler"
              enctype="multipart/form-data"
              novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- ── CV ──────────────────────────────────────────────────────── -->
            <fieldset class="form-group" style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1.5rem;">
                <legend class="form-label" style="padding:0 .5rem;">
                    📄 Curriculum Vitae <span style="color:var(--danger);">*</span>
                </legend>

                <?php if (!empty($cvExistants)): ?>
                    <div style="margin-bottom:1rem;">
                        <label class="form-label" style="font-size:.9rem;">
                            Utiliser un CV déjà déposé :
                        </label>
                        <select name="id_cv_existant" class="form-input" id="cv-existant">
                            <option value="0">— Déposer un nouveau CV —</option>
                            <?php foreach ($cvExistants as $cv): ?>
                                <option value="<?= (int)$cv['Id_cv'] ?>">
                                    <?= htmlspecialchars($cv['Nom_fichier']) ?>
                                    (<?= date('d/m/Y', strtotime($cv['Date_depot'])) ?>)
                                    <?= $cv['Cv_principal'] ? ' ⭐ Principal' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem;">
                        — ou déposer un nouveau CV (PDF, max 5 MB) :
                    </p>
                <?php else: ?>
                    <input type="hidden" name="id_cv_existant" value="0">
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem;">
                        PDF uniquement, max 5 MB
                    </p>
                <?php endif; ?>

                <input type="file"
                       id="cv"
                       name="cv"
                       class="form-input"
                       accept="application/pdf"
                       <?= empty($cvExistants) ? 'required' : '' ?>>
            </fieldset>

            <!-- ── Lettre de motivation PDF ─────────────────────────────────── -->
            <div class="form-group">
                <label for="lm" class="form-label">
                    📝 Lettre de motivation (PDF, optionnel)
                    <small style="font-weight:normal;color:var(--text-muted);">— max 5 MB</small>
                </label>
                <input type="file" id="lm" name="lm" class="form-input" accept="application/pdf">
            </div>

            <!-- ── LM texte libre ───────────────────────────────────────────── -->
            <div class="form-group">
                <label for="lettre_motivation" class="form-label">
                    ✍️ Message de motivation
                    <small style="font-weight:normal;color:var(--text-muted);">— texte libre, optionnel si vous joignez une LM PDF</small>
                </label>
                <textarea id="lettre_motivation"
                          name="lettre_motivation"
                          class="form-input"
                          rows="8"
                          placeholder="Présentez-vous et expliquez votre motivation pour ce stage…"
                ><?= htmlspecialchars($lettre) ?></textarea>
            </div>

            <!-- ── Autres documents ─────────────────────────────────────────── -->
            <div class="form-group">
                <label for="autres" class="form-label">
                    📎 Autres documents (portfolio, diplômes…)
                    <small style="font-weight:normal;color:var(--text-muted);">— PDF uniquement, max 5 MB chacun</small>
                </label>
                <input type="file"
                       id="autres"
                       name="autres[]"
                       class="form-input"
                       accept="application/pdf"
                       multiple>
            </div>

            <!-- ── Actions ──────────────────────────────────────────────────── -->
            <div class="form-actions">
                <a href="/offres/<?= (int)$offre['Id_offre'] ?>" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    Envoyer ma candidature
                </button>
            </div>
        </form>
    </section>
</main>

<script>
// Si un CV existant est sélectionné, le champ file n'est plus obligatoire
const selectCv = document.getElementById('cv-existant');
const inputCv  = document.getElementById('cv');

if (selectCv && inputCv) {
    selectCv.addEventListener('change', () => {
        inputCv.required = selectCv.value === '0';
    });
    // Init
    inputCv.required = selectCv.value === '0';
}
</script>
