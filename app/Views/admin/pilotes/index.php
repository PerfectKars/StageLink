<?php /** @var array $pilotes */ ?>
<main class="container" id="main-content">
    <a href="/admin/utilisateurs" style="color:var(--text-muted);font-size:.9rem;">
        ← Gestion des utilisateurs
    </a>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin:0;">Pilotes</h1>
        <a href="/admin/utilisateurs/creer?type=pilote" class="btn btn--primary">
            + Nouveau pilote
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (empty($pilotes)): ?>
        <p style="color:var(--text-muted);">Aucun pilote.</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
            <?php foreach ($pilotes as $p): ?>
                <a href="/admin/pilotes/<?= (int)$p['Id_pilote'] ?>"
                   style="text-decoration:none;color:inherit;">
                    <article class="card" style="padding:1rem 1.5rem;display:flex;
                                                  justify-content:space-between;align-items:center;
                                                  gap:1rem;flex-wrap:wrap;transition:box-shadow .2s;"
                             onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
                             onmouseleave="this.style.boxShadow=''">
                        <div>
                            <p style="font-weight:600;font-size:.95rem;margin-bottom:.2rem;">
                                🧑‍🏫 <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                            </p>
                            <p style="font-size:.82rem;color:var(--text-muted);">
                                ✉️ <?= htmlspecialchars($p['Email'] ?? '') ?>
                                <?php if (!empty($p['Telephone'])): ?>
                                    &nbsp;|&nbsp; 📞 <?= htmlspecialchars($p['Telephone']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <span style="font-size:.85rem;color:var(--text-muted);">
                                <?= (int)($p['nb_promotions'] ?? 0) ?> promotion(s)
                            </span>
                            <span style="color:var(--primary);font-size:.85rem;">→</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
