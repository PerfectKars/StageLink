<?php $role = $_SESSION['user']['role'] ?? ''; ?>
<section class="section">
    <div class="container">
        <a href="/entreprises" style="color:var(--text-muted);font-size:.9rem;">← Retour aux entreprises</a>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin-top:1rem;"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <div class="offre-detail mt-2">

            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                <div>
                    <div class="offre-detail__header" style="margin-bottom:.5rem;">
                        <h1><?= htmlspecialchars($entreprise['Nom'] ?? '') ?></h1>
                        <?php if (!empty($entreprise['statut_juridique'])): ?>
                            <span class="tag"><?= htmlspecialchars($entreprise['statut_juridique']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($entreprise['moyenne_note'])): ?>
                            <span class="tag">⭐ <?= number_format((float)$entreprise['moyenne_note'], 1) ?>/5</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (in_array($role, ['admin', 'pilote'])): ?>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
                    <a href="/entreprises/<?= (int)$entreprise['Id_entreprise'] ?>/edit"
                       class="btn btn--secondary" style="font-size:.85rem;">✏️ Modifier</a>
                    <?php if ($role === 'admin'): ?>
                    <button type="button" class="btn btn--danger" style="font-size:.85rem;"
                            onclick="confirmerSuppression()">🗑 Supprimer</button>
                    <form id="form-delete" method="POST"
                          action="/entreprises/<?= (int)$entreprise['Id_entreprise'] ?>/delete"
                          style="display:none;">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="offre-detail__info">
                <?php if (!empty($entreprise['SIRET'])): ?>
                <div class="offre-detail__info-item">
                    <label>SIRET</label>
                    <span><?= htmlspecialchars($entreprise['SIRET']) ?></span>
                </div>
                <?php endif; ?>
                <div class="offre-detail__info-item">
                    <label>Email</label>
                    <span><?= htmlspecialchars($entreprise['Email_contact'] ?? 'N/A') ?></span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Téléphone</label>
<span><?= $entreprise['Tel_contact']
    ? chunk_split(htmlspecialchars($entreprise['Tel_contact']), 2, ' ')
    : 'N/A' ?></span>
                </div>
            </div>

            <?php if (!empty($entreprise['sites'])): ?>
            <div class="offre-detail__section mb-2">
                <h2>Siège social & sites</h2>
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.5rem;">
                    <?php foreach ($entreprise['sites'] as $i => $site): ?>
                        <div style="padding:.75rem;background:var(--surface);border-radius:8px;font-size:.9rem;">
                            <strong>📍 <?= $i === 0 ? 'Siège social' : 'Site ' . ($i + 1) ?></strong><br>
                            <?= htmlspecialchars($site['Adresse']) ?>,
                            <?= htmlspecialchars($site['Code_postal']) ?>
                            <?= htmlspecialchars($site['Ville']) ?> —
                            <?= htmlspecialchars($site['Pays']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($entreprise['Description'])): ?>
            <div class="offre-detail__section mb-2">
                <h2>Description</h2>
                <p><?= nl2br(htmlspecialchars($entreprise['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($entreprise['offres'])): ?>
            <div class="offre-detail__section">
                <h2>Offres de stage</h2>
                <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:.75rem;">
                    <?php foreach ($entreprise['offres'] as $offre): ?>
                    <article class="card">
                        <h3 class="card__title">
                            <a href="/offres/<?= (int)$offre['Id_offre'] ?>">
                                <?= htmlspecialchars($offre['titre'] ?? '') ?>
                            </a>
                        </h3>
                        <p class="card__meta">
                            <?php if (!empty($offre['Ville'])): ?>📍 <?= htmlspecialchars($offre['Ville']) ?> &nbsp;|&nbsp;<?php endif; ?>
                            <?php if (!empty($offre['gratification_par_heure'])): ?>💶 <?= number_format((float)$offre['gratification_par_heure'], 2) ?> €/h &nbsp;|&nbsp;<?php endif; ?>
                            <?php if (!empty($offre['date_creation_offre'])): ?>📅 <?= date('d/m/Y', strtotime($offre['date_creation_offre'])) ?><?php endif; ?>
                        </p>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role === 'etudiant'): ?>
            <div class="mt-3">
                <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:.75rem;">Évaluer cette entreprise</h2>
                <form method="POST" action="/entreprises/<?= (int)$entreprise['Id_entreprise'] ?>/noter">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form__group mb-2">
                        <label for="note">Note (1 à 5)</label>
                        <select id="note" name="note">
                            <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?>/5</option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="form__group mb-2">
                        <label for="commentaire">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary">Envoyer l'évaluation</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function confirmerSuppression() {
    const nbOffres = <?= count($entreprise['offres'] ?? []) ?>;
    let msg = 'Supprimer "<?= addslashes($entreprise['Nom'] ?? '') ?>" ?';
    if (nbOffres > 0) {
        msg += '\n\n⚠️ ' + nbOffres + ' offre(s) rattachée(s) seront également supprimée(s).';
    }
    if (confirm(msg)) document.getElementById('form-delete').submit();
}
</script>
