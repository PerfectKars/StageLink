<?php /** @var array $etudiants */ /** @var string $search */ /** @var int $total */ ?>
<main class="container" id="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin:0;">
            Étudiants
            <span style="background:var(--primary);color:#fff;border-radius:99px;
                         padding:.15rem .65rem;font-size:.85rem;margin-left:.5rem;">
                <?= (int)$total ?>
            </span>
        </h1>
        <a href="/admin/utilisateurs/creer?type=etudiant" class="btn btn--primary">
            + Nouvel étudiant
        </a>
    </div>

    <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;">
        <input type="text" name="search" class="form-input" style="max-width:320px;"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Rechercher par nom, prénom, email…">
        <button type="submit" class="btn btn--secondary">Rechercher</button>
        <?php if ($search): ?>
            <a href="/admin/etudiants" class="btn btn--secondary">✕ Effacer</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (empty($etudiants)): ?>
        <p style="color:var(--text-muted);">Aucun étudiant trouvé.</p>
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
                                ✉️ <?= htmlspecialchars($e['Email']) ?>
                                <?php if (!empty($e['promotions'])): ?>
                                    &nbsp;|&nbsp; 📚 <?= htmlspecialchars($e['promotions']) ?>
                                <?php endif; ?>
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
                            <span style="color:var(--primary);font-size:.85rem;">→</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
