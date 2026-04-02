<?php /** @var array $promotions */ ?>
<main class="container" id="main-content">
    <h1 class="page-title">Mes promotions</h1>

    <?php if (empty($promotions)): ?>
        <p style="color:var(--text-muted);">Aucune promotion assignée pour le moment.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-top:1rem;">
            <?php foreach ($promotions as $p): ?>
                <a href="/pilote/promotions/<?= (int)$p['Id_promotion'] ?>"
                   style="text-decoration:none;color:inherit;">
                    <article class="card" style="padding:1.25rem 1.5rem;transition:box-shadow .2s;"
                             onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
                             onmouseleave="this.style.boxShadow=''">
                        <h2 style="font-size:1.05rem;margin-bottom:.4rem;">
                            📚 <?= htmlspecialchars($p['Libelle']) ?>
                        </h2>
                        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem;">
                            <?= htmlspecialchars($p['Filiere'] ?? '') ?> — <?= (int)$p['Annee'] ?>
                        </p>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:.9rem;font-weight:600;">
                                👥 <?= (int)$p['nb_etudiants'] ?> étudiant(s)
                            </span>
                            <span style="font-size:.82rem;color:var(--primary);">Voir →</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php
$baseUrl     = '/pilote/promotions';
$queryParams = '';
include __DIR__ . '/../../../templates/pagination.php';
?>

</main>
