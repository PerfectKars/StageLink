<?php
/** @var array $offre */
$idUtilisateur = (int)($_SESSION['user']['id'] ?? 0);
$userRole      = $_SESSION['user']['role'] ?? '';
?>
<section class="section">
    <div class="container">
        <a href="/offres" style="color:var(--text-muted);font-size:.9rem;">← Retour aux offres</a>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" role="alert" style="margin-top:1rem;">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger" role="alert" style="margin-top:1rem;">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="offre-detail mt-2">

            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
    <div>
        <div class="offre-detail__header" style="margin-bottom:.5rem;">
            <h1><?= htmlspecialchars($offre['Titre'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="offre-detail__company">
                🏢 <a href="/entreprises/<?= (int)($offre['Id_entreprise'] ?? 0) ?>"
                       style="color:inherit;text-decoration:underline;">
                    <?= htmlspecialchars($offre['Nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php if (!empty($offre['statut_juridique'])): ?>
                    <span class="tag" style="font-size:.75rem;margin-left:.5rem;">
                        <?= htmlspecialchars($offre['statut_juridique']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($offre['Ville'])): ?>
                    — 📍 <?= htmlspecialchars($offre['Ville']) ?>
                    <?php if (!empty($offre['Adresse'])): ?>
                        <small style="color:var(--text-muted);font-size:.85rem;">
                            (<?= htmlspecialchars($offre['Adresse']) ?>,
                            <?= htmlspecialchars($offre['Code_postal'] ?? '') ?>)
                        </small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Boutons admin/pilote -->
    <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
        <a href="/offres/<?= (int)$offre['Id_offre'] ?>/edit"
           class="btn btn--secondary" style="font-size:.85rem;">
            ✏️ Modifier
        </a>
        <button type="button" class="btn btn--danger" style="font-size:.85rem;"
                onclick="confirmerSuppressionOffre()">
            🗑 Supprimer
        </button>
        <form id="form-delete-offre" method="POST"
              action="/offres/<?= (int)$offre['Id_offre'] ?>/delete"
              style="display:none;">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        </form>
    </div>
    <?php endif; ?>
</div>

            <div class="offre-detail__info">
                <div class="offre-detail__info-item">
                    <label>Durée</label>
                    <span><?= !empty($offre['duree_mois']) ? (int)$offre['duree_mois'] . ' mois' : '—' ?></span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Rémunération</label>
                    <span><?= number_format((float)($offre['Base_remuneration'] ?? 0), 2) ?> €/h</span>
                </div>
                <div class="offre-detail__info-item">
                    <label>Date de mise en ligne</label>
                    <span><?= !empty($offre['Date_offre']) ? date('d/m/Y', strtotime($offre['Date_offre'])) : '—' ?></span>
                </div>
                <?php if (!empty($offre['date_prevue'])): ?>
                <div class="offre-detail__info-item">
                    <label>Début du stage</label>
                    <span><?= date('d/m/Y', strtotime($offre['date_prevue'])) ?></span>
                </div>
                <?php endif; ?>
                <div class="offre-detail__info-item">
                    <label>Candidatures</label>
                    <span><?= (int)($offre['nb_candidatures'] ?? 0) ?></span>
                </div>
                <?php if (!empty($offre['SIRET'])): ?>
                <div class="offre-detail__info-item">
                    <label>SIRET employeur</label>
                    <span><?= htmlspecialchars($offre['SIRET']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($offre['competences'])): ?>
<div class="offre-detail__section mb-2">
    <h2>Compétences requises</h2>

    <div class="card__tags" id="competences-list">

        <?php 
        $max = 3;
        $total = count($offre['competences']);
        ?>

        <?php foreach ($offre['competences'] as $index => $comp): ?>
            <span class="tag competence-item <?= $index >= $max ? 'hidden' : '' ?>">
                <?= htmlspecialchars($comp['Nom_competence'] ?? $comp['Libelle'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
        <?php endforeach; ?>

        <?php if ($total > $max): ?>
            <span class="tag more-btn" onclick="toggleCompetences()">
                +<?= $total - $max ?> voir plus
            </span>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>



<?php if ($userRole === 'etudiant'): ?>
<div class="mt-3">
    <?php
    $dejaPostule = false;
    $statutCandidature = null;
    if ($idUtilisateur > 0) {
        $candidatureModel  = new \App\Models\CandidatureModel();
        $dejaPostule       = $candidatureModel->aDejaPostule($idUtilisateur, (int)($offre['Id_offre'] ?? 0));
        if ($dejaPostule) {
            $statutCandidature = $candidatureModel->getStatutCandidature(
                $idUtilisateur, (int)($offre['Id_offre'] ?? 0)
            );
        }
    }
    ?>

    <?php if ($statutCandidature === 'Confirmé'): ?>
        <div class="alert alert-success">
            🎉 Vous avez confirmé ce stage !
        </div>

    <?php elseif ($statutCandidature === 'Accepté'): ?>
        <div style="padding:1.25rem;background:#d1fae5;border-radius:8px;border:1px solid #6ee7b7;">
            <p style="font-size:.95rem;color:#065f46;font-weight:700;margin-bottom:.5rem;">
                🎉 Félicitations, votre candidature a été acceptée !
            </p>
            <p style="font-size:.85rem;color:#065f46;margin-bottom:1rem;">
                ⚠️ En confirmant ce stage, toutes vos autres candidatures en cours
                seront automatiquement refusées.
            </p>
            <form method="POST"
                  action="/mes-candidatures/<?= (int)$offre['Id_offre'] ?>/confirmer">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn--primary">
                    ✅ Confirmer ce stage
                </button>
            </form>
        </div>

    <?php elseif ($statutCandidature === 'Refusé'): ?>
        <div class="alert alert-danger">
            ❌ Votre candidature a été refusée.
        </div>

    <?php elseif ($statutCandidature === 'Entretien'): ?>
        <div style="padding:1rem;background:#fef3c7;border-radius:8px;border:1px solid #fcd34d;">
            <p style="color:#92400e;font-size:.9rem;">
                📞 Entretien en cours — nous vous contacterons prochainement.
            </p>
        </div>

    <?php elseif ($dejaPostule): ?>
        <div class="alert alert-success">
            ✅ Vous avez déjà postulé à cette offre.
            <a href="/mes-candidatures" style="margin-left:.5rem;">Voir mes candidatures →</a>
        </div>

    <?php else: ?>
        <a href="/offres/<?= (int)($offre['Id_offre'] ?? 0) ?>/postuler"
           class="btn btn--primary" style="display:inline-block;margin-top:.5rem;">
            Postuler à cette offre →
        </a>
    <?php endif; ?>

    <!-- Wishlist -->
    <div style="margin-top:1rem;">
        <?php
        $wishlistModel = new \App\Models\WishlistModel();
        $enWishlist    = $wishlistModel->exists($idUtilisateur, (int)($offre['Id_offre'] ?? 0));
        ?>
        <?php if ($enWishlist): ?>
            <form method="POST" action="/wishlist/remove" style="display:inline;">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id_offre" value="<?= (int)$offre['Id_offre'] ?>">
                <input type="hidden" name="redirect" value="/offres/<?= (int)$offre['Id_offre'] ?>">
                <button type="submit" class="btn btn--secondary">❤️ Retirer de ma wishlist</button>
            </form>
        <?php else: ?>
            <form method="POST" action="/wishlist/add" style="display:inline;">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id_offre" value="<?= (int)$offre['Id_offre'] ?>">
                <input type="hidden" name="redirect" value="/offres/<?= (int)$offre['Id_offre'] ?>">
                <button type="submit" class="btn btn--secondary">🤍 Ajouter à ma wishlist</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

        
            <?php if ($userRole === 'etudiant'): ?>
<div class="mt-3">
    <?php
    $dejaPostule = false;
    if ($idUtilisateur > 0) {
        $candidatureModel = new \App\Models\CandidatureModel();
        $dejaPostule = $candidatureModel->aDejaPostule($idUtilisateur, (int)($offre['Id_offre'] ?? 0));
    }
    ?>
    <?php if ($dejaPostule): ?>
        <div class="alert alert-success">
            ✅ Vous avez déjà postulé à cette offre.
            <a href="/mes-candidatures" style="margin-left:.5rem;">Voir mes candidatures →</a>
        </div>
    <?php else: ?>
        <a href="/offres/<?= (int)($offre['Id_offre'] ?? 0) ?>/postuler"
           class="btn btn--primary"
           style="display:inline-block;margin-top:.5rem;">
            Postuler à cette offre →
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>
        </div>
    </div>

    <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
<script>
function confirmerSuppressionOffre() {
    const titre = '<?= addslashes($offre['Titre'] ?? '') ?>';
    const nb    = <?= (int)($offre['nb_candidatures'] ?? 0) ?>;
    let msg = `Supprimer l'offre "${titre}" ?`;
    if (nb > 0) {
        msg += `\n\n⚠️ ${nb} candidature(s) associée(s) seront également supprimées.`;
    }
    if (confirm(msg)) document.getElementById('form-delete-offre').submit();
}

<!-- Toggle statut -->
<form method="POST" action="/offres/<?= (int)$offre['Id_offre'] ?>/statut" style="display:inline;">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <?php if (($offre['statut'] ?? 'active') === 'active'): ?>
        <button type="submit" class="btn btn--secondary" style="font-size:.85rem;"
                onclick="return confirm('Désactiver cette offre ?')">
            ⏸ Désactiver
        </button>
    <?php else: ?>
        <button type="submit" class="btn btn--primary" style="font-size:.85rem;">
            ▶ Réactiver
        </button>
    <?php endif; ?>
</form>
</script>
<?php endif; ?>
</section>


<script>
function toggleCompetences() {
    const items = document.querySelectorAll('.competence-item.hidden');
    items.forEach(el => el.classList.remove('hidden'));

    const btn = document.querySelector('.more-btn');
    if (btn) btn.style.display = 'none';
}
</script>