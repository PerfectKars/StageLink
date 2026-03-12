<section class="section">
    <div class="container">
        <h1>Entreprises</h1>

        <form method="GET" action="/entreprises" class="search-form">
            <input type="text" name="nom" placeholder="Nom de l'entreprise"
                   value="<?= htmlspecialchars($filters['nom'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn--primary">Rechercher</button>
        </form>

        <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'pilote'])): ?>
            <a href="/entreprises/create" class="btn btn--secondary">+ Créer une entreprise</a>
        <?php endif; ?>

        <?php if (empty($entreprises)): ?>
            <p class="empty-state">Aucune entreprise trouvée.</p>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($entreprises as $e): ?>
                    <article class="card">
                        <h2 class="card__title">
                            <a href="/entreprises/<?= $e['Id_entreprise'] ?>">
                                <?= htmlspecialchars($e['Nom'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <p><?= htmlspecialchars($e['Email_contact'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
