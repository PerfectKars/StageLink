<?php /** @var array $promotions */ ?>
<main class="container" id="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin:0;">
            Promotions
            <span style="background:var(--primary);color:#fff;border-radius:99px;
                         padding:.15rem .65rem;font-size:.85rem;margin-left:.5rem;">
                <?= (int)$total ?>
            </span>
        </h1>
        <a href="/admin/promotions/create" class="btn btn--primary">+ Nouvelle promotion</a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (empty($promotions)): ?>
        <p style="color:var(--text-muted);">Aucune promotion.</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
            <?php foreach ($promotions as $p): ?>
                <a href="/admin/promotions/<?= (int)$p['Id_promotion'] ?>"
                   style="text-decoration:none;color:inherit;">
                    <article class="card" style="padding:1rem 1.5rem;display:flex;
                                                  justify-content:space-between;align-items:center;
                                                  gap:1rem;flex-wrap:wrap;transition:box-shadow .2s;"
                             onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
                             onmouseleave="this.style.boxShadow=''">
                        <div>
                            <p style="font-weight:600;font-size:.95rem;margin-bottom:.2rem;">
                                📚 <?= htmlspecialchars($p['Libelle']) ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);">
                                <?= htmlspecialchars($p['Filiere'] ?? '') ?> — <?= htmlspecialchars($p['Annee'] ?? '') ?>
                                <?php if (!empty($p['pilote_prenom'])): ?>
                                    &nbsp;|&nbsp; 🧑‍🏫 <?= htmlspecialchars($p['pilote_prenom'] . ' ' . $p['pilote_nom']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <span style="font-size:.85rem;color:var(--text-muted);">
                                👥 <?= (int)$p['nb_etudiants'] ?> étudiant(s)
                            </span>
                            <span style="color:var(--primary);font-size:.85rem;">→</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $baseUrl     = '/admin/promotions';
    $queryParams = '';
    include __DIR__ . '/../../../../templates/pagination.php';
    ?>
</main>