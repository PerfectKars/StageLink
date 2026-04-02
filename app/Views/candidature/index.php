<?php /** @var array $candidatures */ ?>
<main class="container" id="main-content">
    <h1 class="page-title">Mes candidatures</h1>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (empty($candidatures)): ?>
        <div class="empty-state" style="text-align:center;padding:3rem 0;">
            <p style="color:var(--text-muted);margin-bottom:1rem;">
                Vous n'avez encore postulé à aucune offre.
            </p>
            <a href="/offres" class="btn btn-primary">Parcourir les offres</a>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">
            <?= count($candidatures) ?> candidature<?= count($candidatures) > 1 ? 's' : '' ?>
        </p>
        <div style="display:flex;flex-direction:column;gap:1rem;">
            <?php foreach ($candidatures as $c): ?>
                <?php
                $statut      = $c['Statut'] ?? 'En attente';
                $statutStyle = match($statut) {
                    'Accepté'   => 'background:#d1fae5;color:#065f46;',
                    'Refusé'    => 'background:#fee2e2;color:#991b1b;',
                    'Entretien' => 'background:#fef3c7;color:#92400e;',
                    default     => 'background:var(--surface);color:var(--text-muted);',
                };
                ?>



                <article class="card" style="padding:1.25rem 1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                        <div style="flex:1;">
                            <h2 style="font-size:1.05rem;margin-bottom:.35rem;">
                                <a href="/offres/<?= (int)$c['Id_offre'] ?>">
                                    <?= htmlspecialchars($c['Titre'] ?? '') ?>
                                </a>
                            </h2>
                            <p class="card__meta">
                                🏢 <a href="/entreprises/<?= (int)$c['Id_entreprise'] ?>">
                                    <?= htmlspecialchars($c['Nom_entreprise'] ?? '') ?>
                                </a>
                                <?php if (!empty($c['Ville'])): ?>
                                    &nbsp;|&nbsp; 📍 <?= htmlspecialchars($c['Ville']) ?>
                                <?php endif; ?>
                                <?php if (!empty($c['duree_mois'])): ?>
                                    &nbsp;|&nbsp; 🗓 <?= (int)$c['duree_mois'] ?> mois
                                <?php endif; ?>
                                <?php if (!empty($c['cv_chemin'] ?? '') || !empty($c['cv_nom'] ?? '')): ?>
    <div style="margin-top:.5rem;">
        <a href="/mes-candidatures/cv/<?= (int)$c['Id_offre'] ?>"
           target="_blank"
           style="font-size:.82rem;color:var(--primary);">
            📄 Voir mon CV →
        </a>
    </div>
<?php endif; ?>
                                <?php if (!empty($c['Base_remuneration'])): ?>
                                    &nbsp;|&nbsp; 💶 <?= number_format((float)$c['Base_remuneration'], 2) ?> €/h
                                <?php endif; ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);margin-top:.35rem;">
                                Postulé le <?= !empty($c['Date_candidature'])
                                    ? date('d/m/Y', strtotime($c['Date_candidature'])) : '—' ?>
                            </p>
                        </div>

                        <!-- Badge statut -->
                        <span style="padding:.35rem .85rem;border-radius:99px;font-size:.82rem;font-weight:600;white-space:nowrap;<?= $statutStyle ?>">
                            <?= htmlspecialchars($statut) ?>
                        </span>
                    </div>

                    <?php if (!empty($c['Lettre_motivation'])): ?>
                        <details style="margin-top:.75rem;">
                            <summary style="cursor:pointer;font-size:.88rem;color:var(--text-muted);">
                                Voir le message de motivation
                            </summary>
                            <p style="margin-top:.5rem;font-size:.9rem;white-space:pre-wrap;padding:.75rem;background:var(--surface);border-radius:6px;">
                                <?= htmlspecialchars($c['Lettre_motivation']) ?>
                            </p>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
            <?php
$baseUrl = '/mes-candidatures';
$queryParams = '';
include __DIR__ . '/../../../templates/pagination.php';
?>

    <?php endif; ?>

</main>
