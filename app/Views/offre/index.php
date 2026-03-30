<?php
/** @var array  $offres */
/** @var array  $filters */
/** @var int    $total */
/** @var int    $page */
/** @var int    $perPage */
$role        = $_SESSION['user']['role'] ?? '';
$isAdminPilote = in_array($role, ['admin', 'pilote']);

// Récupérer la liste des compétences pour les checkboxes
$offreModel  = new \App\Models\OffreModel();
$competences = $offreModel->findAllCompetences();

// Compétences sélectionnées (tableau)
$selectedComps = isset($filters['competences']) ? (array) $filters['competences'] : [];
?>
<section class="section">
    <div class="container">
        <h1 style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;margin-bottom:1.5rem;">
            Recherche d'offres de stage
        </h1>

        <!-- Formulaire de recherche -->
        <div class="search-form" style="background:var(--surface);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
            <form method="GET" action="/offres" id="form-recherche">

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <!-- Titre -->
                    <div>
                        <label style="font-size:.82rem;color:var(--text-muted);display:block;margin-bottom:.3rem;">
                            Titre de l'offre
                        </label>
                        <input type="text" name="titre" class="form-input"
                               placeholder="Ex : Développeur PHP"
                               value="<?= htmlspecialchars($filters['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Ville avec autocomplétion -->
                    <div style="position:relative;">
                        <label style="font-size:.82rem;color:var(--text-muted);display:block;margin-bottom:.3rem;">
                            Ville
                        </label>
                        <input type="text" name="ville" id="input-ville" class="form-input"
                               autocomplete="off"
                               placeholder="Ex : Paris"
                               value="<?= htmlspecialchars($filters['ville'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <ul id="ville-suggestions"
                            style="display:none;position:absolute;z-index:100;background:#fff;
                                   border:1px solid var(--border);border-radius:8px;list-style:none;
                                   margin:0;padding:.25rem 0;width:100%;
                                   box-shadow:0 4px 12px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;">
                        </ul>
                    </div>
                </div>

                <!-- Compétences checkboxes -->
                <?php if (!empty($competences)): ?>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:.82rem;color:var(--text-muted);display:block;margin-bottom:.5rem;">
                        Compétences requises
                    </label>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                        <?php foreach ($competences as $c): ?>
                            <label style="display:flex;align-items:center;gap:.35rem;
                                          padding:.35rem .75rem;border-radius:99px;cursor:pointer;
                                          font-size:.85rem;border:1px solid var(--border);
                                          background:<?= in_array($c['Nom_competence'], $selectedComps) ? 'var(--primary)' : 'var(--surface)' ?>;
                                          color:<?= in_array($c['Nom_competence'], $selectedComps) ? '#fff' : 'inherit' ?>;"
                                   id="label-comp-<?= (int)$c['Id_competence'] ?>">
                                <input type="checkbox"
                                       name="competences[]"
                                       value="<?= htmlspecialchars($c['Nom_competence']) ?>"
                                       style="display:none;"
                                       <?= in_array($c['Nom_competence'], $selectedComps) ? 'checked' : '' ?>
                                       onchange="toggleCompLabel(this, <?= (int)$c['Id_competence'] ?>)">
                                <?= htmlspecialchars($c['Nom_competence']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                    <?php if (!empty($filters['titre']) || !empty($filters['ville']) || !empty($selectedComps)): ?>
                        <a href="/offres" class="btn btn--secondary">✕ Effacer</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn--primary">Rechercher</button>
                </div>
            </form>
        </div>

        <?php if ($isAdminPilote): ?>
            <a href="/offres/create" class="btn btn--secondary" style="margin-bottom:1rem;display:inline-block;">
                + Créer une offre
            </a>
        <?php endif; ?>

        <p style="font-size:.88rem;color:var(--text-muted);margin-bottom:1rem;">
            <?= $total ?> offre<?= $total > 1 ? 's' : '' ?> trouvée<?= $total > 1 ? 's' : '' ?>
        </p>

        <?php if (empty($offres)): ?>
            <p class="empty-state">Aucune offre ne correspond à votre recherche.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach ($offres as $offre): ?>
                    <?php $inactive = ($offre['statut'] ?? 'active') === 'inactive'; ?>
                    <article class="card" style="<?= $inactive ? 'opacity:.5;border-left:3px solid #dc2626;' : '' ?>">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
                            <h2 class="card__title" style="margin-bottom:.3rem;">
                                <a href="/offres/<?= (int)$offre['Id_offre'] ?>">
                                    <?= htmlspecialchars($offre['Titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h2>
                            <?php if ($inactive && $isAdminPilote): ?>
                                <span style="background:#fee2e2;color:#991b1b;font-size:.72rem;
                                             padding:.2rem .55rem;border-radius:99px;font-weight:600;
                                             white-space:nowrap;">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="card__company">
                            <?= htmlspecialchars($offre['Nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="card__meta">
                            <span class="card__meta-item">
                                📍 <?= htmlspecialchars($offre['Ville'] ?? 'Non précisé', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php if (!empty($offre['duree_mois'])): ?>
                                <span class="card__meta-item">
                                    🗓 <?= (int)$offre['duree_mois'] ?> mois
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($offre['Base_remuneration'])): ?>
                                <span class="card__meta-item">
                                    💶 <?= number_format((float)$offre['Base_remuneration'], 2) ?> €/h
                                </span>
                            <?php endif; ?>
                            <span class="card__meta-item">
                                👥 <?= (int)($offre['nb_candidatures'] ?? 0) ?> candidature(s)
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php
            $totalPages = (int) ceil($total / $perPage);
            if ($totalPages > 1):
            ?>
            <nav class="pagination" aria-label="Pagination" style="margin-top:1.5rem;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
                       class="pagination__item <?= $i === $page ? 'pagination__item--active' : '' ?>"
                       <?= $i === $page ? 'aria-current="page"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script>
// ── Autocomplétion ville ─────────────────────────────────────────────────────
const inputVille  = document.getElementById('input-ville');
const villeSugg   = document.getElementById('ville-suggestions');
let villeTimer;

inputVille.addEventListener('input', () => {
    clearTimeout(villeTimer);
    const q = inputVille.value.trim();
    if (q.length < 2) { villeSugg.style.display = 'none'; return; }

    villeTimer = setTimeout(async () => {
        try {
            const res  = await fetch(
                `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=6&type=municipality`
            );
            const data = await res.json();
            villeSugg.innerHTML = '';
            if (!data.features.length) { villeSugg.style.display = 'none'; return; }

            data.features.forEach(f => {
                const p  = f.properties;
                const li = document.createElement('li');
                li.textContent = `${p.city} (${p.postcode})`;
                li.style.cssText = 'padding:.6rem 1rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid var(--border);';
                li.addEventListener('mouseenter', () => li.style.background = 'var(--surface)');
                li.addEventListener('mouseleave', () => li.style.background = '');
                li.addEventListener('click', () => {
                    inputVille.value = p.city;
                    villeSugg.style.display = 'none';
                });
                villeSugg.appendChild(li);
            });
            villeSugg.style.display = 'block';
        } catch { villeSugg.style.display = 'none'; }
    }, 300);
});

document.addEventListener('click', e => {
    if (!inputVille.contains(e.target) && !villeSugg.contains(e.target))
        villeSugg.style.display = 'none';
});

// ── Toggle visuel checkboxes compétences ────────────────────────────────────
function toggleCompLabel(checkbox, id) {
    const label = document.getElementById('label-comp-' + id);
    if (checkbox.checked) {
        label.style.background = 'var(--primary)';
        label.style.color      = '#fff';
        label.style.borderColor = 'var(--primary)';
    } else {
        label.style.background  = 'var(--surface)';
        label.style.color       = 'inherit';
        label.style.borderColor = 'var(--border)';
    }
}
</script>