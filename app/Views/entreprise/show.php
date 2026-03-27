<section class="section">
    <div class="container">
        <a href="/entreprises" style="color:var(--text-muted);font-size:.9rem;">← Retour aux entreprises</a>

        <div class="offre-detail mt-2">
            <div class="offre-detail__header">
                <h1><?= htmlspecialchars($entreprise['Nom'], ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (!empty($entreprise['moyenne_note'])): ?>
                    <span class="tag">⭐ <?= number_format((float)$entreprise['moyenne_note'], 1) ?>/5</span>
                <?php endif; ?>
            </div>

            <div class="offre-detail__info">
                <div class="offre-detail__info-item">
                    <label>Email</label>
                    <span><?= htmlspecialchars($entreprise['Email_contact'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Téléphone</label>
                    <span><?= htmlspecialchars($entreprise['Tel_contact'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

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
                                <a href="/offres/<?= $offre['Id_offre'] ?>">
                                    <?= htmlspecialchars($offre['Titre'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h3>
                            <p class="card__meta">
                                <span><?= number_format((float)$offre['Base_remuneration'], 2) ?> €/mois</span>
                                <span><?= date('d/m/Y', strtotime($offre['Date_offre'])) ?></span>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'etudiant'): ?>
            <div class="mt-3">
                <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:.75rem;">
                    Évaluer cette entreprise
                </h2>
                <form method="POST" action="/entreprises/<?= $entreprise['Id_entreprise'] ?>/noter">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form__group mb-2">
                        <label for="note">Note (1 à 5)</label>
                        <select id="note" name="note">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?>/5</option>
                            <?php endfor; ?>
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
