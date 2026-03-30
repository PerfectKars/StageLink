<?php
/** @var array $offres */
$offres = $offres ?? [];
?>

<main class="container" id="main-content">
    <h1 class="page-title">Ma liste de souhaits</h1>

    <?php if (empty($offres)): ?>
        <p class="empty-state">Votre liste est vide. <a href="/offres">Parcourir les offres</a></p>
    <?php else: ?>
        <div class="offres-grid">
            <?php foreach ($offres as $offre): ?>
                <article class="offre-card">
                    <h2 class="offre-card__title">
                        <a href="/offres/<?= (int) $offre['Id_offre'] ?>">
                            <?= htmlspecialchars($offre['Titre']) ?>
                        </a>
                    </h2>
                    <p class="offre-card__entreprise">
                        <a href="/entreprises/<?= (int) $offre['Id_entreprise'] ?>">
                            <?= htmlspecialchars($offre['Nom_entreprise']) ?>
                        </a>
                    </p>
                    <?php if (!empty($offre['Base_remuneration'])): ?>
                        <p class="offre-card__remuneration">
                            <?= number_format((float) $offre['Base_remuneration'], 2, ',', ' ') ?> €/h
                        </p>
                    <?php endif; ?>
                    <p class="offre-card__date">
                        Publié le <?= htmlspecialchars($offre['Date_offre'] ?? '') ?>
                    </p>

                    <form method="POST" action="/wishlist/remove" class="wishlist-remove-form">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id_offre"
                               value="<?= (int) $offre['Id_offre'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            Retirer de la liste
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
