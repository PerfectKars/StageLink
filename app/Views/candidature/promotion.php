<?php
/** @var array $candidatures */
/** @var array $promotions */
$parEtudiant = [];
foreach ($candidatures as $c) {
    $key = $c['Id_etudiant'];
    if (!isset($parEtudiant[$key])) {
        $parEtudiant[$key] = [
            'nom'          => $c['etudiant_nom'],
            'prenom'       => $c['etudiant_prenom'],
            'promotion'    => $c['promotion'],
            'candidatures' => [],
        ];
    }
    $parEtudiant[$key]['candidatures'][] = $c;
}
?>
<main class="container" id="main-content">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin:0;">Candidatures de ma promotion</h1>
    </div>

    <?php if (!empty($promotions)): ?>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <?php foreach ($promotions as $promo): ?>
                <span class="tag" style="font-size:.85rem;">
                    📚 <?= htmlspecialchars($promo['Libelle']) ?> — <?= (int)$promo['Annee'] ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($parEtudiant)): ?>
        <div style="text-align:center;padding:3rem 0;color:var(--text-muted);">
            Aucune candidature pour le moment dans votre promotion.
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">
            <?= count($parEtudiant) ?> étudiant(s) — <?= count($candidatures) ?> candidature(s) au total
        </p>

        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <?php foreach ($parEtudiant as $idEt => $data): ?>
                <div class="card" style="padding:1.25rem 1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
                        <div>
                            <h2 style="font-size:1.05rem;margin:0;">
                                👤 <?= htmlspecialchars($data['prenom'] . ' ' . $data['nom']) ?>
                            </h2>
                            <p style="font-size:.82rem;color:var(--text-muted);margin:.2rem 0 0;">
                                <?= htmlspecialchars($data['promotion'] ?? '') ?> —
                                <?= count($data['candidatures']) ?> candidature(s)
                            </p>
                        </div>
                        <a href="/pilote/candidatures/<?= (int)$idEt ?>"
                           class="btn btn--secondary" style="font-size:.85rem;">
                            Voir le détail →
                        </a>
                    </div>

                    <!-- Aperçu des 3 dernières candidatures -->
                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        <?php foreach (array_slice($data['candidatures'], 0, 3) as $c): ?>
                            <?php
                            $statut      = $c['Statut'] ?? 'En attente';
                            $statutStyle = match($statut) {
                                'Accepté'   => 'background:#d1fae5;color:#065f46;',
                                'Refusé'    => 'background:#fee2e2;color:#991b1b;',
                                'Entretien' => 'background:#fef3c7;color:#92400e;',
                                default     => 'background:var(--surface);color:var(--text-muted);',
                            };
                            ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;
                                        padding:.65rem .9rem;background:var(--bg);border-radius:6px;gap:.5rem;flex-wrap:wrap;">
                                <div>
                                    <span style="font-size:.9rem;font-weight:600;">
                                        <a href="/offres/<?= (int)$c['Id_offre'] ?>" style="color:inherit;">
                                            <?= htmlspecialchars($c['Titre'] ?? '') ?>
                                        </a>
                                    </span>
                                    <span style="font-size:.82rem;color:var(--text-muted);margin-left:.5rem;">
                                        — <?= htmlspecialchars($c['Nom_entreprise'] ?? '') ?>
                                        <?php if (!empty($c['Ville'])): ?> · <?= htmlspecialchars($c['Ville']) ?><?php endif; ?>
                                    </span>
                                </div>
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <?php if (!empty($c['cv_nom'])): ?>
                                        <span style="font-size:.78rem;color:var(--text-muted);">📄 CV joint</span>
                                    <?php endif; ?>
                                    <span style="padding:.25rem .65rem;border-radius:99px;font-size:.78rem;font-weight:600;<?= $statutStyle ?>">
                                        <?= htmlspecialchars($statut) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($data['candidatures']) > 3): ?>
                            <p style="font-size:.82rem;color:var(--text-muted);text-align:center;">
                                + <?= count($data['candidatures']) - 3 ?> autre(s) —
                                <a href="/pilote/candidatures/<?= (int)$idEt ?>">voir tout</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
