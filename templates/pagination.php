<?php
/**
 * Component Pagination - Version compacte
 */

$totalPages = ceil($total / $perPage);
if ($totalPages <= 1) return;

$queryString = !empty($queryParams) ? '&' . $queryParams : '';
?>

<div style="display:flex;justify-content:center;align-items:center;gap:0.25rem;margin-top:1.5rem;flex-wrap:wrap;">
    <!-- Précédent -->
    <?php if ($page > 1): ?>
        <a href="<?= $baseUrl ?>?page=<?= $page - 1 ?><?= $queryString ?>"
           style="padding:0.4rem 0.6rem;font-size:.8rem;color:var(--primary);text-decoration:none;border-radius:4px;transition:all .2s;"
           onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
            ←
        </a>
    <?php endif; ?>

    <!-- Numéros -->
    <?php
    $start = max(1, $page - 1);
    $end   = min($totalPages, $page + 1);
    
    if ($start > 1): ?>
        <a href="<?= $baseUrl ?>?page=1<?= $queryString ?>" 
           style="padding:0.4rem 0.6rem;font-size:.8rem;color:var(--primary);text-decoration:none;border-radius:4px;transition:all .2s;"
           onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">1</a>
        <?php if ($start > 2): ?><span style="color:var(--text-muted);font-size:.75rem;padding:0 .2rem;">…</span><?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $page): ?>
            <span style="padding:0.4rem 0.6rem;font-size:.8rem;background:var(--primary);color:#fff;border-radius:4px;font-weight:600;">
                <?= $i ?>
            </span>
        <?php else: ?>
            <a href="<?= $baseUrl ?>?page=<?= $i ?><?= $queryString ?>"
               style="padding:0.4rem 0.6rem;font-size:.8rem;color:var(--primary);text-decoration:none;border-radius:4px;transition:all .2s;"
               onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
                <?= $i ?>
            </a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?><span style="color:var(--text-muted);font-size:.75rem;padding:0 .2rem;">…</span><?php endif; ?>
        <a href="<?= $baseUrl ?>?page=<?= $totalPages ?><?= $queryString ?>"
           style="padding:0.4rem 0.6rem;font-size:.8rem;color:var(--primary);text-decoration:none;border-radius:4px;transition:all .2s;"
           onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
            <?= $totalPages ?>
        </a>
    <?php endif; ?>

    <!-- Suivant -->
    <?php if ($page < $totalPages): ?>
        <a href="<?= $baseUrl ?>?page=<?= $page + 1 ?><?= $queryString ?>"
           style="padding:0.4rem 0.6rem;font-size:.8rem;color:var(--primary);text-decoration:none;border-radius:4px;transition:all .2s;"
           onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
            →
        </a>
    <?php endif; ?>
</div>

<p style="text-align:center;color:var(--text-muted);font-size:.85rem;margin-top:1rem;">
    Page <?= $page ?> sur <?= $totalPages ?>
</p>
