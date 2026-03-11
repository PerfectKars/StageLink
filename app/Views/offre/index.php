<section class="section">
    <div class="container">
        <h1>Offres de stage</h1>

        <form method="GET" action="/offres" class="search-form">
            <input type="text" name="titre" placeholder="Titre de l'offre"
                   value="<?= htmlspecialchars($filters['titre'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="text" name="ville" placeholder="Ville"
                   value="<?= htmlspecialchars($filters['ville'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="text" name="competence" placeholder="Compétence"
                   value="<?= htmlspecialchars($filters['competence'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn--primary">Rechercher</button>
        </form>

        <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'pilote'])): ?>
            <a href="/offres/create" class="btn btn--secondary">+ Créer une offre</a>
        <?php endif; ?>

        <?php if (empty($offres)): ?>
            <p class="empty-state">Aucune offre ne correspond à votre recherche.</p>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($offres as $offre): ?>
                    <article class="card">
                        <h2 class="card__title">
                            <a href="/offres/<?= $offre['id_offre'] ?>">
                                <?= htmlspecialchars($offre['titre'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <p><?= htmlspecialchars($offre['raison_sociale'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars($offre['ville_stage'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= $offre['duree_mois'] ?> mois · <?= $offre['nb_candidatures'] ?> candidature(s)</p>
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
