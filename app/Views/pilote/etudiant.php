<?php
/** @var array      $etudiant */
/** @var array      $candidatures */
/** @var array      $cvs */
?>
<main class="container" id="main-content">

    <a href="javascript:history.back()" style="color:var(--text-muted);font-size:.9rem;">
        ← Retour
    </a>

    <!-- En-tête étudiant -->
    <div style="margin:1.25rem 0 1.5rem;">
        <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.3rem;">
            👤 <?= htmlspecialchars(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?>
        </h1>
        <p style="color:var(--text-muted);font-size:.9rem;">
            ✉️ <?= htmlspecialchars($etudiant['Email'] ?? '') ?>
            <?php if (!empty($etudiant['Telephone'])): ?>
                &nbsp;|&nbsp; 📞 <?= htmlspecialchars($etudiant['Telephone']) ?>
            <?php endif; ?>
            <?php if (!empty($etudiant['promotions'])): ?>
                &nbsp;|&nbsp; 📚 <?= htmlspecialchars($etudiant['promotions']) ?>
            <?php endif; ?>
            <?php if (!empty($etudiant['Statut_recherche'])): ?>
                &nbsp;|&nbsp;
                <span class="tag" style="font-size:.78rem;">
                    <?= htmlspecialchars($etudiant['Statut_recherche']) ?>
                </span>
            <?php endif; ?>
        </p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;">

        <!-- CV -->
        <div class="card" style="padding:1.25rem 1.5rem;">
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                📄 CV déposés
            </h2>
            <?php if (empty($cvs)): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucun CV déposé.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.65rem;">
                    <?php foreach ($cvs as $cv): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;
                                    padding:.65rem .85rem;background:var(--surface);border-radius:6px;">
                            <div>
                                <p style="font-size:.88rem;font-weight:<?= $cv['Cv_principal'] ? '700' : '400' ?>;">
                                    <?= htmlspecialchars($cv['Nom_fichier']) ?>
                                    <?php if ($cv['Cv_principal']): ?>
                                        <span style="font-size:.75rem;color:var(--primary);margin-left:.35rem;">⭐ Principal</span>
                                    <?php endif; ?>
                                </p>
                                <p style="font-size:.78rem;color:var(--text-muted);">
                                    Déposé le <?= date('d/m/Y', strtotime($cv['Date_depot'])) ?>
                                </p>
                            </div>
                            <?php if (file_exists($cv['Chemin_fichier'])): ?>
                                <a href="/pilote/cv/0/<?= (int)$etudiant['Id_etudiant'] ?>?cv=<?= (int)$cv['Id_cv'] ?>"
                                   target="_blank"
                                   style="font-size:.78rem;color:var(--primary);white-space:nowrap;">
                                    Voir →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Candidatures -->
        <div>
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                📋 Candidatures (<?= count($candidatures) ?>)
            </h2>

            <?php if (empty($candidatures)): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucune candidature.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.85rem;">
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
                        <article class="card" style="padding:1rem 1.25rem;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem;flex-wrap:wrap;">
                                <div style="flex:1;">
                                    <h3 style="font-size:.95rem;margin-bottom:.25rem;">
                                        <a href="/offres/<?= (int)$c['Id_offre'] ?>">
                                            <?= htmlspecialchars($c['Titre'] ?? '') ?>
                                        </a>
                                    </h3>
                                    <p style="font-size:.82rem;color:var(--text-muted);">
                                        🏢 <?= htmlspecialchars($c['Nom_entreprise'] ?? '') ?>
                                        <?php if (!empty($c['Ville'])): ?> — 📍 <?= htmlspecialchars($c['Ville']) ?><?php endif; ?>
                                        <?php if (!empty($c['duree_mois'])): ?> — 🗓 <?= (int)$c['duree_mois'] ?> mois<?php endif; ?>
                                    </p>
                                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">
                                        Postulé le <?= !empty($c['Date_candidature']) ? date('d/m/Y', strtotime($c['Date_candidature'])) : '—' ?>
                                    </p>
                                </div>
                                <span style="padding:.3rem .75rem;border-radius:99px;font-size:.78rem;font-weight:600;white-space:nowrap;<?= $statutStyle ?>">
                                    <?= htmlspecialchars($statut) ?>
                                </span>
                            </div>

                            <div style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                                <?php if (!empty($c['cv_chemin']) && file_exists($c['cv_chemin'])): ?>
                                    <a href="/pilote/cv/<?= (int)$c['Id_offre'] ?>/<?= (int)$etudiant['Id_etudiant'] ?>"
                                       target="_blank"
                                       class="btn btn--secondary" style="font-size:.78rem;padding:.3rem .7rem;">
                                        📄 CV
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($c['Lettre_motivation'])): ?>
                                    <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'"
                                            class="btn btn--secondary" style="font-size:.78rem;padding:.3rem .7rem;">
                                        ✍️ Message
                                    </button>
                                    <div style="display:none;width:100%;margin-top:.5rem;
                                                padding:.75rem;background:var(--surface);border-radius:6px;
                                                font-size:.88rem;white-space:pre-wrap;">
                                        <?= htmlspecialchars($c['Lettre_motivation']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
