<section class="section">
    <div class="container">
        <a href="/offres" style="color:var(--text-muted);font-size:.9rem;">← Retour aux offres</a>
        <div class="offre-detail mt-2">
            <div class="offre-detail__header">
                <h1><?= htmlspecialchars($offre['Titre'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="offre-detail__company">
                    🏢 <?= htmlspecialchars($offre['Nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($offre['Ville'])): ?>
                        — 📍 <?= htmlspecialchars($offre['Ville'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="offre-detail__info">
                <div class="offre-detail__info-item">
                    <label>Rémunération</label>
                    <span><?= number_format((float)($offre['Base_remuneration'] ?? 0), 2) ?> €/h</span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Date de mise en ligne</label>
                    <span><?= !empty($offre['Date_offre']) ? date('d/m/Y', strtotime($offre['Date_offre'])) : '—' ?></span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Candidatures</label>
                    <span><?= (int)($offre['nb_candidatures'] ?? 0) ?></span>
                </div>
            </div>

            <?php if (!empty($offre['competences'])): ?>
                <div class="offre-detail__section mb-2">
                    <h2>Compétences requises</h2>
                    <div class="card__tags">
                        <?php foreach ($offre['competences'] as $comp): ?>
                            <span class="tag">
                                <?= htmlspecialchars($comp['Nom_competence'] ?? $comp['Libelle'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="offre-detail__section">
                <h2>Description du stage</h2>
                <p><?= nl2br(htmlspecialchars($offre['Description'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
            </div>

            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'etudiant'): ?>
                <div class="mt-3">
                    <form method="POST" action="/offres/<?= (int)$offre['Id_offre'] ?>/postuler">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form__group mb-2">
                            <label for="lettre">Lettre de motivation</label>
                            <textarea id="lettre" name="lettre_motivation" rows="6" placeholder="Votre lettre de motivation..."></textarea>
                        </div>
                        <button type="submit" class="btn btn--primary">Postuler à cette offre</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>