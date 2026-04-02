<!-- Chargement de Chart.js + Données pour JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    window.statsData = {
        total: <?= (int)($stats['total'] ?? 0) ?>,
        actives: <?= (int)($stats['actives'] ?? 0) ?>,
        companies: <?= json_encode($stats['par_entreprise'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS) ?>,
        skills: <?= json_encode($stats['par_competence'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS) ?>
    };
</script>

<?php /** @var array $stats */ ?>
<main class="container" id="main-content">
    <h1 class="page-title">Statistiques des offres</h1>

    <!-- Carrousel -->
    <div class="carousel" style="margin-bottom:2rem;">
        
        <!-- Boutons de navigation rectangles -->
        <div class="carousel__nav" style="display:flex;gap:1rem;margin-bottom:1.5rem;justify-content:center;">
            <button class="carousel__nav-btn carousel__nav-btn--active" 
                    data-slide="0"
                    style="padding:0.75rem 1.5rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius);cursor:pointer;font-weight:600;transition:all 0.3s;font-size:.95rem;">
                📊 Détail offres
            </button>
            <button class="carousel__nav-btn" 
                    data-slide="1"
                    style="padding:0.75rem 1.5rem;background:var(--border);color:var(--text);border:none;border-radius:var(--radius);cursor:pointer;font-weight:600;transition:all 0.3s;font-size:.95rem;">
                🏢 Entreprises
            </button>
            <button class="carousel__nav-btn" 
                    data-slide="2"
                    style="padding:0.75rem 1.5rem;background:var(--border);color:var(--text);border:none;border-radius:var(--radius);cursor:pointer;font-weight:600;transition:all 0.3s;font-size:.95rem;">
                🛠 Compétences
            </button>
        </div>

        <!-- Slides -->
        <div class="carousel__viewport" style="border-radius:var(--radius);background:var(--surface);border:1px solid var(--border);overflow:hidden;">
            
            <!-- Slide 1 : Chiffres clés -->
            <div class="carousel__slide" style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;padding:2rem;opacity:1;transition:opacity 0.3s;align-items:center;min-height:400px;">
                <!-- Gauche : Rectangles avec stats -->
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <div style="padding:1.5rem;background:var(--primary);color:#fff;border-radius:var(--radius);text-align:center;font-weight:600;font-size:1.05rem;">
                        📊 Total offres
                        <p style="font-size:2.5rem;font-weight:800;margin:.5rem 0 0;">
                            <?= (int)($stats['total'] ?? 0) ?>
                        </p>
                    </div>
                    <div style="padding:1.5rem;background:#10b981;color:#fff;border-radius:var(--radius);text-align:center;font-weight:600;font-size:1.05rem;">
                        ✅ Offres actives
                        <p style="font-size:2.5rem;font-weight:800;margin:.5rem 0 0;">
                            <?= (int)($stats['actives'] ?? 0) ?>
                        </p>
                    </div>
                    <div style="padding:1.5rem;background:#6366f1;color:#fff;border-radius:var(--radius);text-align:center;font-weight:600;font-size:1.05rem;">
                        ⏸ Offres inactives
                        <p style="font-size:2.5rem;font-weight:800;margin:.5rem 0 0;">
                            <?= (int)($stats['total'] ?? 0) - (int)($stats['actives'] ?? 0) ?>
                        </p>
                    </div>
                </div>
                <!-- Droite : Graphique barres -->
                <div>
                    <canvas id="chartOverview" style="max-height:350px;"></canvas>
                </div>
            </div>

            <!-- Slide 2 : Offres par entreprise -->
<div class="carousel__slide" style="display:none;opacity:0;transition:opacity 0.3s;padding:2rem;min-height:400px;">
    <div style="display:grid;grid-template-columns:380px 1fr;gap:2.5rem;align-items:center;min-height:380px;">
        <!-- Liste à gauche -->
        <div>
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;">🏢 Offres par entreprise</h2>
            <div style="display:flex;flex-direction:column;gap:.75rem;max-height:380px;overflow-y:auto;">
                <?php
                $max = max(array_column($stats['par_entreprise'], 'nb')) ?: 1;
                foreach ($stats['par_entreprise'] as $idx => $row):
                    $pct = round(((int)$row['nb'] / $max) * 100);
                ?>
                    <div class="company-item" data-idx="<?= $idx ?>" 
                         style="padding:.75rem;border-radius:var(--radius);background:var(--bg);border:1px solid var(--border);cursor:pointer;transition:all 0.2s;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.25rem;">
                            <strong style="font-size:.95rem;"><?= htmlspecialchars($row['Nom']) ?></strong>
                            <span style="background:var(--primary);color:#fff;padding:.25rem .5rem;border-radius:4px;font-size:.8rem;font-weight:600;">
                                <?= (int)$row['nb'] ?>
                            </span>
                        </div>
                        <div style="background:var(--surface);border-radius:99px;height:4px;">
                            <div style="background:var(--primary);border-radius:99px;height:4px;width:<?= $pct ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Graphique à droite - CENTRE VERTICALEMENT -->
        <div style="display:flex;align-items:center;justify-content:center;height:100%;min-height:380px;">
            <div style="width:100%;max-width:520px;">
                <canvas id="chartCompanies" style="max-height:340px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Slide 3 : Compétences -->
<div class="carousel__slide" style="display:none;opacity:0;transition:opacity 0.3s;padding:2rem;min-height:400px;">
    <div style="display:grid;grid-template-columns:380px 1fr;gap:2.5rem;align-items:center;min-height:380px;">
        <!-- Liste à gauche -->
        <div>
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;">🛠 Compétences demandées</h2>
            <div style="display:flex;flex-direction:column;gap:.75rem;max-height:380px;overflow-y:auto;">
                <?php
                $max = max(array_column($stats['par_competence'], 'nb')) ?: 1;
                foreach ($stats['par_competence'] as $idx => $row):
                    $pct = round(((int)$row['nb'] / $max) * 100);
                ?>
                    <div class="skill-item" data-idx="<?= $idx ?>" 
                         style="padding:.75rem;border-radius:var(--radius);background:var(--bg);border:1px solid var(--border);cursor:pointer;transition:all 0.2s;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.25rem;">
                            <strong style="font-size:.95rem;"><?= htmlspecialchars($row['Libelle']) ?></strong>
                            <span style="background:#6366f1;color:#fff;padding:.25rem .5rem;border-radius:4px;font-size:.8rem;font-weight:600;">
                                <?= (int)$row['nb'] ?>
                            </span>
                        </div>
                        <div style="background:var(--surface);border-radius:99px;height:4px;">
                            <div style="background:#6366f1;border-radius:99px;height:4px;width:<?= $pct ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Graphique à droite - CENTRE VERTICALEMENT -->
        <div style="display:flex;align-items:center;justify-content:center;height:100%;min-height:380px;">
            <div style="width:100%;max-width:520px;">
                <canvas id="chartSkills" style="max-height:340px;"></canvas>
            </div>
        </div>
    </div>
</div>


        </div>
    </div>

</main>
