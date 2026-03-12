<section class="hero">
    <div class="container">
        <h1>Trouvez votre stage idéal</h1>
        <p class="hero__sub">
            <?= $nbOffres ?> offres disponibles dans <?= $nbEntreprises ?> entreprises partenaires.
        </p>
        <a href="/offres" class="btn btn--primary btn--lg">Parcourir les offres</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Dernières offres publiées</h2>
        <div class="cards-grid">
            <?php foreach ($dernieresOffres as $offre): ?>
                <article class="card">
                    <h3 class="card__title">
                        <a href="/offres/<?= $offre['id_offre'] ?>">
                            <?= htmlspecialchars($offre['titre'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                    <p class="card__meta"><?= htmlspecialchars($offre['raison_sociale'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="card__meta"><?= htmlspecialchars($offre['ville_stage'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="card__meta">
                        <?= $offre['duree_mois'] ?> mois —
                        <?= number_format((float)$offre['gratification'], 2) ?> €/mois
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="/offres" class="btn btn--outline">Voir toutes les offres</a>
        </div>
    </div>
</section>
