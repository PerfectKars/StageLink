<?php /** @var array $promotion */ /** @var array $etudiants */ ?>
<main class="container" id="main-content">
    <a href="javascript:history.back()" style="color:var(--text-muted);font-size:.9rem;">← Retour</a>

    <div style="margin:1.25rem 0 1.5rem;">
        <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.3rem;">
            📚 <?= htmlspecialchars($promotion['Libelle'] ?? '') ?>
        </h1>
        <p style="color:var(--text-muted);font-size:.9rem;">
            <?= htmlspecialchars($promotion['Filiere'] ?? '') ?> —
            <?= htmlspecialchars($promotion['Annee'] ?? '') ?>
            <?php if (!empty($promotion['pilote_prenom'])): ?>
                &nbsp;|&nbsp; 🧑‍🏫 Pilote :
                <?= htmlspecialchars($promotion['pilote_prenom'] . ' ' . $promotion['pilote_nom']) ?>
            <?php endif; ?>
        </p>
    </div>

    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">
        👥 Étudiants (<?= count($etudiants) ?>)
    </h2>

    <?php if (empty($etudiants)): ?>
        <p style="color:var(--text-muted);">Aucun étudiant dans cette promotion.</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
            <?php foreach ($etudiants as $e): ?>
                <a href="/admin/etudiants/<?= (int)$e['Id_etudiant'] ?>"
                   style="text-decoration:none;color:inherit;">
                    <article class="card" style="padding:1rem 1.5rem;display:flex;
                                                  justify-content:space-between;align-items:center;
                                                  gap:1rem;flex-wrap:wrap;transition:box-shadow .2s;"
                             onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
                             onmouseleave="this.style.boxShadow=''">
                        <div>
                            <p style="font-weight:600;font-size:.95rem;margin-bottom:.2rem;">
                                <?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);">
                                ✉️ <?= htmlspecialchars($e['Email'] ?? '') ?>
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <?php if (!empty($e['Statut_recherche'])): ?>
                                <span class="tag" style="font-size:.78rem;">
                                    <?= htmlspecialchars($e['Statut_recherche']) ?>
                                </span>
                            <?php endif; ?>
                            <span style="font-size:.85rem;color:var(--text-muted);">
                                <?= (int)$e['nb_candidatures'] ?> candidature(s)
                            </span>
                            <span style="color:var(--primary);">→</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
