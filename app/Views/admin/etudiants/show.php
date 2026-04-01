<?php /** @var array $etudiant */ ?>
<main class="container" id="main-content">

    <a href="/admin/etudiants" style="color:var(--text-muted);font-size:.9rem;">← Liste des étudiants</a>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-top:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin:1.25rem 0 1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.3rem;">
                🎓 <?= htmlspecialchars(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?>
            </h1>
            <p style="color:var(--text-muted);font-size:.9rem;">
                ✉️ <?= htmlspecialchars($etudiant['Email'] ?? '') ?>
                <?php if (!empty($etudiant['Telephone'])): ?>
                    &nbsp;|&nbsp; 📞 <?= htmlspecialchars($etudiant['Telephone']) ?>
                <?php endif; ?>
                <?php if (!empty($etudiant['date_creation'])): ?>
                    &nbsp;|&nbsp; 🗓 Créé le <?= date('d/m/Y', strtotime($etudiant['date_creation'])) ?>
                <?php endif; ?>
            </p>
            <?php if (!empty($etudiant['promotions'])): ?>
                <p style="font-size:.85rem;margin-top:.35rem;">
                    📚 <?= htmlspecialchars($etudiant['promotions']) ?>
                </p>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="/admin/etudiants/<?= (int)$etudiant['Id_etudiant'] ?>/edit"
               class="btn btn--secondary" style="font-size:.85rem;">✏️ Modifier</a>
            <button type="button" class="btn btn--danger" style="font-size:.85rem;"
                    onclick="if(confirm('Supprimer cet étudiant ? Toutes ses candidatures seront supprimées.'))
                             document.getElementById('form-del').submit()">
                🗑 Supprimer
            </button>
            <form id="form-del" method="POST"
                  action="/admin/etudiants/<?= (int)$etudiant['Id_etudiant'] ?>/delete"
                  style="display:none;">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;">

        <!-- CV -->
        <div class="card" style="padding:1.25rem 1.5rem;">
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                📄 CV déposés (<?= count($etudiant['cvs'] ?? []) ?>)
            </h2>
            <?php if (empty($etudiant['cvs'])): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucun CV.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <?php foreach ($etudiant['cvs'] as $cv): ?>
                        <div style="padding:.65rem .85rem;background:var(--surface);border-radius:6px;
                                    display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <p style="font-size:.85rem;font-weight:<?= $cv['Cv_principal'] ? '700' : '400' ?>;">
                                    <?= htmlspecialchars($cv['Nom_fichier']) ?>
                                    <?php if ($cv['Cv_principal']): ?>
                                        <span style="color:var(--primary);font-size:.75rem;"> ⭐</span>
                                    <?php endif; ?>
                                </p>
                                <p style="font-size:.75rem;color:var(--text-muted);">
                                    <?= date('d/m/Y', strtotime($cv['Date_depot'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Candidatures -->
        <div>
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                📋 Candidatures (<?= count($etudiant['candidatures'] ?? []) ?>)
            </h2>
            <?php if (empty($etudiant['candidatures'])): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucune candidature.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.75rem;">
                    <?php foreach ($etudiant['candidatures'] as $c): ?>
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
                                <div>
                                    <h3 style="font-size:.95rem;margin-bottom:.2rem;">
                                        <a href="/offres/<?= (int)$c['Id_offre'] ?>">
                                            <?= htmlspecialchars($c['Titre'] ?? '') ?>
                                        </a>
                                    </h3>
                                    <p style="font-size:.82rem;color:var(--text-muted);">
                                        🏢 <?= htmlspecialchars($c['Nom_entreprise'] ?? '') ?>
                                        &nbsp;|&nbsp;
                                        📅 <?= !empty($c['Date_candidature']) ? date('d/m/Y', strtotime($c['Date_candidature'])) : '—' ?>
                                        <?php if (!empty($c['cv_nom'])): ?>
                                            &nbsp;|&nbsp; 📄 <?= htmlspecialchars($c['cv_nom']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span style="padding:.3rem .75rem;border-radius:99px;font-size:.78rem;font-weight:600;<?= $statutStyle ?>">
                                    <?= htmlspecialchars($statut) ?>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
