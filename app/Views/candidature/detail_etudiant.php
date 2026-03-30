<?php
/** @var array      $etudiant */
/** @var array      $candidatures */
$baseUrl = 'https://stagelink.local';
?>
<main class="container" id="main-content">

    <a href="/pilote/candidatures" style="color:var(--text-muted);font-size:.9rem;">
        ← Retour aux candidatures
    </a>

    <div style="margin:1.5rem 0 1rem;">
        <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.35rem;">
            👤 <?= htmlspecialchars(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?>
        </h1>
        <p style="color:var(--text-muted);font-size:.9rem;">
            <?php if (!empty($etudiant['Email'])): ?>
                ✉️ <?= htmlspecialchars($etudiant['Email']) ?>
            <?php endif; ?>
            <?php if (!empty($etudiant['Telephone'])): ?>
                &nbsp;|&nbsp; 📞 <?= htmlspecialchars($etudiant['Telephone']) ?>
            <?php endif; ?>
            <?php if (!empty($etudiant['promotion'])): ?>
                &nbsp;|&nbsp; 📚 <?= htmlspecialchars($etudiant['promotion']) ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if (empty($candidatures)): ?>
        <p style="color:var(--text-muted);">Cet étudiant n'a encore postulé à aucune offre.</p>
    <?php else: ?>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">
            <?= count($candidatures) ?> candidature(s)
        </p>

        <div style="display:flex;flex-direction:column;gap:1.25rem;">
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
                            <h2 style="font-size:1rem;margin-bottom:.3rem;">
                                <a href="/offres/<?= (int)$c['Id_offre'] ?>">
                                    <?= htmlspecialchars($c['Titre'] ?? '') ?>
                                </a>
                            </h2>
                            <p style="font-size:.85rem;color:var(--text-muted);">
                                🏢 <?= htmlspecialchars($c['Nom_entreprise'] ?? '') ?>
                                <?php if (!empty($c['Ville'])): ?> — 📍 <?= htmlspecialchars($c['Ville']) ?><?php endif; ?>
                                <?php if (!empty($c['duree_mois'])): ?> — 🗓 <?= (int)$c['duree_mois'] ?> mois<?php endif; ?>
                                <?php if (!empty($c['Base_remuneration'])): ?> — 💶 <?= number_format((float)$c['Base_remuneration'], 2) ?> €/h<?php endif; ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem;">
                                Postulé le <?= !empty($c['Date_candidature']) ? date('d/m/Y', strtotime($c['Date_candidature'])) : '—' ?>
                            </p>
                        </div>
                        <span style="padding:.35rem .85rem;border-radius:99px;font-size:.82rem;font-weight:600;white-space:nowrap;<?= $statutStyle ?>">
                            <?= htmlspecialchars($statut) ?>
                        </span>
                    </div>

                    <!-- Modifier statut -->
<form method="POST"
      action="/candidatures/<?= (int)$c['Id_offre'] ?>/<?= (int)$etudiant['Id_etudiant'] ?>/statut"
      style="display:inline-flex;align-items:center;gap:.5rem;margin-top:.75rem;">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="redirect"
           value="/pilote/etudiants/<?= (int)$etudiant['Id_etudiant'] ?>">
    <select name="statut" class="form-input" style="font-size:.82rem;padding:.3rem .6rem;width:auto;">
        <?php foreach (['En attente','Entretien','Accepté','Refusé'] as $s): ?>
            <option value="<?= $s ?>" <?= $statut === $s ? 'selected' : '' ?>>
                <?= $s ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn--primary" style="font-size:.78rem;padding:.3rem .7rem;">
        Mettre à jour
    </button>
</form>

                    <!-- Fichiers joints -->
                    <div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">
                        <?php if (!empty($c['cv_chemin']) && file_exists($c['cv_chemin'])): ?>
                            <a href="/pilote/cv/<?= (int)$c['Id_offre'] ?>/<?= (int)($etudiant['Id_etudiant'] ?? 0) ?>"
                               class="btn btn--secondary" style="font-size:.82rem;padding:.35rem .8rem;"
                               target="_blank">
                                📄 Télécharger le CV
                            </a>
                        <?php else: ?>
                            <span style="font-size:.82rem;color:var(--text-muted);">Pas de CV joint</span>
                        <?php endif; ?>
                    </div>

                    <!-- Lettre de motivation texte -->
                    <?php if (!empty($c['Lettre_motivation'])): ?>
                        <details style="margin-top:.75rem;">
                            <summary style="cursor:pointer;font-size:.88rem;color:var(--text-muted);">
                                Voir le message de motivation
                            </summary>
                            <p style="margin-top:.5rem;font-size:.9rem;white-space:pre-wrap;
                                      padding:.75rem;background:var(--surface);border-radius:6px;">
                                <?= htmlspecialchars($c['Lettre_motivation']) ?>
                            </p>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
