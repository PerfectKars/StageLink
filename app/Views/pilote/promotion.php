<?php
/** @var array $promotion */
/** @var array $etudiants */
?>
<main class="container" id="main-content">

    <a href="/pilote/promotions" style="color:var(--text-muted);font-size:.9rem;">
        ← Mes promotions
    </a>

    <div style="margin:1.25rem 0 1.5rem;">
        <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.3rem;">
            📚 <?= htmlspecialchars($promotion['Libelle'] ?? '') ?>
        </h1>
        <p style="color:var(--text-muted);font-size:.9rem;">
            <?= htmlspecialchars($promotion['Filiere'] ?? '') ?> —
            Année <?= (int)($promotion['Annee'] ?? 0) ?> —
            <?= count($etudiants) ?> étudiant(s)
        </p>
    </div>

    <?php if (empty($etudiants)): ?>
        <p style="color:var(--text-muted);">Aucun étudiant dans cette promotion.</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
            <?php foreach ($etudiants as $e): ?>
                <a href="/pilote/etudiants/<?= (int)$e['Id_etudiant'] ?>"
                   style="text-decoration:none;color:inherit;">
                    <article class="card" style="padding:1rem 1.5rem;display:flex;justify-content:space-between;
                                                  align-items:center;gap:1rem;flex-wrap:wrap;
                                                  transition:box-shadow .2s;"
                             onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
                             onmouseleave="this.style.boxShadow=''">
                        <div>
                            <p style="font-weight:600;font-size:.95rem;margin-bottom:.2rem;">
                                👤 <?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);">
                                ✉️ <?= htmlspecialchars($e['Email'] ?? '') ?>
                                <?php if (!empty($e['Telephone'])): ?>
                                    &nbsp;|&nbsp; 📞 <?= htmlspecialchars($e['Telephone']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <?php if (!empty($e['Statut_recherche'])): ?>
                                <span class="tag" style="font-size:.78rem;">
                                    <?= htmlspecialchars($e['Statut_recherche']) ?>
                                </span>
                            <?php endif; ?>
                            <span style="font-size:.85rem;font-weight:600;">
                                <?= (int)$e['nb_candidatures'] ?> candidature(s)
                            </span>
                            <span style="color:var(--primary);font-size:.85rem;">Voir →</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
$baseUrl     = '/pilote/promotions/' . (int)$promotion['Id_promotion'];
$queryParams = '';
include __DIR__ . '/../../../templates/pagination.php';
?>

</main>
