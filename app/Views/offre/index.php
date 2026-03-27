<section class="section">
    <div class="container">
        <h1 style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;margin-bottom:1.5rem;">
            Recherche d'offres de stage
        </h1>

        <div class="search-form">
            <form method="GET" action="/offres" style="display:flex;flex-wrap:wrap;gap:.75rem;width:100%">
                <input type="text" name="titre" placeholder="Titre de l'offre"
                       style="flex:1;min-width:180px"
                       value="<?= htmlspecialchars($filters['titre'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="text" name="ville" placeholder="Ville"
                       style="flex:1;min-width:140px"
                       value="<?= htmlspecialchars($filters['ville'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="text" name="competence" placeholder="Compétence"
                       style="flex:1;min-width:140px"
                       value="<?= htmlspecialchars($filters['competence'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn--primary">Rechercher</button>
            </form>
        </div>

        <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'pilote'])): ?>
            <a href="/offres/create" class="btn btn--secondary mb-2">+ Créer une offre</a>
        <?php endif; ?>

        <p style="font-size:.88rem;color:var(--text-muted);margin-bottom:1rem;">
            <?= $total ?> offre<?= $total > 1 ? 's' : '' ?> trouvée<?= $total > 1 ? 's' : '' ?>
        </p>

        <?php if (empty($offres)): ?>
            <p class="empty-state">Aucune offre ne correspond à votre recherche.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach ($offres as $offre): ?>
                    <article class="card">
                        <h2 class="card__title">
                            <a href="/offres/<?= $offre['Id_offre'] ?>">
                                <?= htmlspecialchars($offre['Titre'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <p class="card__company"><?= htmlspecialchars($offre['raison_sociale'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="card__meta">
                            <span class="card__meta-item">📍 <?= htmlspecialchars($offre['Ville'] ?? 'Non précisé', ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="card__meta-item">💶 <?= number_format((float)($offre['Base_remuneration'] ?? 0), 2) ?> €/mois</span>
                            <span class="card__meta-item">👥 <?= $offre['nb_candidatures'] ?> candidature(s)</span>
                        </div>
                        <?php if (!empty($offre['competences'])): ?>
                        <div class="card__tags">
                            <?php foreach (array_slice($offre['competences'], 0, 4) as $comp): ?>
                                <span class="tag"><?= htmlspecialchars($comp['Libelle'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php
            $totalPages = (int) ceil($total / $perPage);
            if ($totalPages > 1):
            ?>
            <nav class="pagination" aria-label="Pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
                       class="pagination__item <?= $i === $page ? 'pagination__item--active' : '' ?>"
                       <?= $i === $page ? 'aria-current="page"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>