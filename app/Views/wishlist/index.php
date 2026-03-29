<?php /** @var array $offres */ ?>
<main class="container" id="main-content">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin:0;">
            Ma liste de souhaits
            <?php if (!empty($offres)): ?>
                <span style="background:var(--primary);color:#fff;border-radius:99px;padding:.15rem .65rem;font-size:.85rem;margin-left:.5rem;">
                    <?= count($offres) ?>
                </span>
            <?php endif; ?>
        </h1>
        <a href="/offres" style="font-size:.9rem;color:var(--primary);">
            ← Parcourir les offres
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (empty($offres)): ?>
        <div style="text-align:center;padding:3rem 0;">
            <p style="color:var(--text-muted);margin-bottom:1rem;">
                Votre liste de souhaits est vide.
            </p>
            <a href="/offres" class="btn btn-primary">Parcourir les offres</a>
        </div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:1rem;">
            <?php foreach ($offres as $offre): ?>
                <article class="card" style="padding:1.25rem 1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">

                        <div style="flex:1;min-width:0;">
                            <h2 style="font-size:1.05rem;margin-bottom:.35rem;">
                                <a href="/offres/<?= (int)$offre['Id_offre'] ?>">
                                    <?= htmlspecialchars($offre['Titre'] ?? '') ?>
                                </a>
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
                            <p style="font-size:.82rem;color:var(--text-muted);margin-top:.35rem;">
                                Ajouté le <?= !empty($offre['Date_ajout'])
                                    ? date('d/m/Y', strtotime($offre['Date_ajout'])) : '—' ?>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div style="display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;">
                            <a href="/offres/<?= (int)$offre['Id_offre'] ?>/postuler"
                               class="btn btn--primary"
                               style="font-size:.85rem;padding:.4rem .9rem;white-space:nowrap;">
                                Postuler →
                            </a>
                            <form method="POST" action="/wishlist/remove">
                                <input type="hidden" name="csrf_token"
                                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id_offre"
                                       value="<?= (int)$offre['Id_offre'] ?>">
                                <input type="hidden" name="redirect" value="/wishlist">
                                <button type="submit"
                                        style="background:none;border:none;color:var(--text-muted);font-size:.82rem;cursor:pointer;padding:.25rem 0;"
                                        onclick="return confirm('Retirer cette offre de la wishlist ?')">
                                    ✕ Retirer
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
