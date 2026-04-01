<?php /** @var array $stats */ ?>
<main class="container" id="main-content">
    <h1 class="page-title">Statistiques des offres</h1>

    <!-- Chiffres clés -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="card" style="padding:1.25rem;text-align:center;">
            <p style="font-size:2rem;font-weight:800;color:var(--primary);margin:0;">
                <?= (int)($stats['total'] ?? 0) ?>
            </p>
            <p style="font-size:.85rem;color:var(--text-muted);margin:.25rem 0 0;">Offres totales</p>
        </div>
        <div class="card" style="padding:1.25rem;text-align:center;">
            <p style="font-size:2rem;font-weight:800;color:#10b981;margin:0;">
                <?= (int)($stats['actives'] ?? 0) ?>
            </p>
            <p style="font-size:.85rem;color:var(--text-muted);margin:.25rem 0 0;">
    <span class="dot dot-active"></span> Offres actives
</p>
        </div>
        <div class="card" style="padding:1.25rem;text-align:center;">
            <p style="font-size:2rem;font-weight:800;color:#6366f1;margin:0;">
                <?= (int)($stats['total'] ?? 0) - (int)($stats['actives'] ?? 0) ?>
            </p>
            <p style="font-size:.85rem;color:var(--text-muted);margin:.25rem 0 0;">
    <span class="dot dot-inactive"></span> Offres inactives
</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        <!-- Par entreprise -->
        <div class="card" style="padding:1.25rem 1.5rem;">
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                🏢 Offres par entreprise
            </h2>
            <?php if (empty($stats['par_entreprise'])): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucune donnée.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <?php
                    $max = max(array_column($stats['par_entreprise'], 'nb')) ?: 1;
                    foreach ($stats['par_entreprise'] as $row):
                        $pct = round(((int)$row['nb'] / $max) * 100);
                    ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.2rem;">
                                <span><?= htmlspecialchars($row['Nom']) ?></span>
                                <strong><?= (int)$row['nb'] ?></strong>
                            </div>
                            <div style="background:var(--surface);border-radius:99px;height:6px;">
                                <div style="background:var(--primary);border-radius:99px;height:6px;width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Par compétence -->
        <div class="card" style="padding:1.25rem 1.5rem;">
            <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
                🛠 Compétences les plus demandées
            </h2>
            <?php if (empty($stats['par_competence'])): ?>
                <p style="color:var(--text-muted);font-size:.9rem;">Aucune donnée.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <?php
                    $max = max(array_column($stats['par_competence'], 'nb')) ?: 1;
                    foreach ($stats['par_competence'] as $row):
                        $pct = round(((int)$row['nb'] / $max) * 100);
                    ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.2rem;">
                                <span><?= htmlspecialchars($row['Libelle']) ?></span>
                                <strong><?= (int)$row['nb'] ?></strong>
                            </div>
                            <div style="background:var(--surface);border-radius:99px;height:6px;">
                                <div style="background:#6366f1;border-radius:99px;height:6px;width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
