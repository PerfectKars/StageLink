<?php
/** @var array  $user */
/** @var array  $candidatures */
/** @var int    $totalCandidatures */
$totalCandidatures = $totalCandidatures ?? 0;
$role              = $_SESSION['user']['role'] ?? '';
?>
<section class="section">
    <div class="container">

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin-bottom:1rem;">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <h1 style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;margin-bottom:1.5rem;">
            Mon profil
        </h1>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;">

            <!-- ── Infos personnelles ──────────────────────────────────────── -->
            <div class="card">
                <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:1.25rem;">
                    Informations personnelles
                </h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/profil" class="form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form__group">
                        <label>Nom</label>
                        <input type="text" name="nom"
                               value="<?= htmlspecialchars($user['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form__group">
                        <label>Prénom</label>
                        <input type="text" name="prenom"
                               value="<?= htmlspecialchars($user['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form__group">
                        <label>Email</label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($user['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form__group">
                        <label>
                            Nouveau mot de passe
                            <span style="color:var(--text-muted);font-size:.8rem;">
                                (laisser vide pour ne pas changer)
                            </span>
                        </label>
                        <input type="password" name="mot_de_passe" placeholder="••••••••">
                    </div>

                    <?php if ($role === 'etudiant'): ?>
<div class="form__group">
    <?php $photo = $_SESSION['user']['photo'] ?? null; ?>
    <?php if ($photo): ?>
        <div style="margin-bottom:.75rem;">
            <img src="/uploads/photos/<?= htmlspecialchars($photo) ?>"
                 alt="Photo de profil"
                 style="width:80px;height:80px;border-radius:50%;
                        object-fit:cover;border:2px solid var(--border);">
        </div>
    <?php endif; ?>
    <label>Photo de profil
        <small style="color:var(--text-muted);font-weight:normal;">
            — JPG/PNG/WEBP, max 2MB
        </small>
    </label>
    <input type="file" name="photo"
           accept="image/jpeg,image/png,image/webp"
           class="form-input">
</div>
<?php endif; ?>

                    <button type="submit" class="btn btn--primary btn--full">Sauvegarder</button>
                </form>
            </div>

            <!-- ── Colonne droite ──────────────────────────────────────────── -->
            <div style="display:flex;flex-direction:column;gap:1rem;">

                <!-- Candidatures (étudiant seulement) -->
                <?php if ($role === 'etudiant'): ?>
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                        <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin:0;">
                            Mes candidatures
                            <span style="background:var(--primary);color:#fff;border-radius:99px;padding:.15rem .6rem;font-size:.8rem;margin-left:.5rem;">
                                <?= $totalCandidatures ?>
                            </span>
                        </h2>
                        <?php if ($totalCandidatures > 0): ?>
                            <a href="/mes-candidatures"
                               style="font-size:.85rem;color:var(--primary);">
                                Voir toutes →
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($candidatures)): ?>
                        <p style="color:var(--text-muted);padding:1rem 0;font-size:.9rem;">
                            Aucune candidature pour le moment.
                            <a href="/offres">Parcourir les offres →</a>
                        </p>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:.65rem;">
                            <?php foreach ($candidatures as $c): ?>
                                <?php
                                $statut      = $c['Statut'] ?? 'En attente';
                                $statutStyle = match($statut) {
                                    'Accepté'   => 'background:#d1fae5;color:#065f46;',
                                    'Refusé'    => 'background:#fee2e2;color:#991b1b;',
                                    'Entretien' => 'background:#fef3c7;color:#92400e;',
                                    default     => 'background:var(--surface);color:var(--text-muted);',
                                };
                                ?>
                                <div style="padding:.85rem;background:var(--bg);border-radius:var(--radius-sm);display:flex;justify-content:space-between;align-items:center;gap:.75rem;">
                                    <div style="flex:1;min-width:0;">
                                        <p style="font-weight:600;font-size:.95rem;margin-bottom:.15rem;">
                                            <a href="/offres/<?= (int)$c['Id_offre'] ?>"
                                               style="color:inherit;">
                                                <?= htmlspecialchars($c['Titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </p>
                                        <p style="font-size:.82rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($c['Nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($c['Ville'])): ?>
                                                — <?= htmlspecialchars($c['Ville']) ?>
                                            <?php endif; ?>
                                            — <?= !empty($c['Date_candidature'])
                                                ? date('d/m/Y', strtotime($c['Date_candidature']))
                                                : '—' ?>
                                        </p>
                                    </div>
                                    <span style="padding:.3rem .75rem;border-radius:99px;font-size:.78rem;font-weight:600;white-space:nowrap;<?= $statutStyle ?>">
                                        <?= htmlspecialchars($statut) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($totalCandidatures > 5): ?>
                            <div style="text-align:center;margin-top:1rem;">
                                <a href="/mes-candidatures" class="btn btn--secondary" style="font-size:.88rem;">
                                    Voir les <?= $totalCandidatures - 5 ?> autres candidatures
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Mon compte / actions rapides -->
                <div class="card">
                    <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:.75rem;">
                        Mon compte
                    </h2>
                    <p style="font-size:.9rem;color:var(--text-muted);">
                        Rôle : <span class="tag"><?= htmlspecialchars($role) ?></span>
                    </p>

                    <?php if ($role === 'admin'): ?>
<div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
    <a href="/admin/utilisateurs"   class="btn btn--secondary">Gérer les utilisateurs</a>
    <a href="/offres/create"        class="btn btn--secondary">Créer une offre</a>
    <a href="/entreprises/create"   class="btn btn--secondary">Créer une entreprise</a>
    <a href="/admin/promotions/create" class="btn btn--secondary">Créer une promotion</a>
</div>
<?php endif; ?>

                    <?php if ($role === 'pilote'): ?>
<div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
    <a href="/pilote/promotions"     class="btn btn--secondary">Mes promotions</a>
    <a href="/pilote/etudiants/create" class="btn btn--secondary">Créer un étudiant</a>
    <a href="/offres/create"         class="btn btn--secondary">Créer une offre</a>
    <a href="/entreprises/create"    class="btn btn--secondary">Créer une entreprise</a>
</div>
<?php endif; ?>

                    <?php if ($role === 'etudiant'): ?>
                    <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
                        <a href="/mes-candidatures" class="btn btn--secondary">Toutes mes candidatures</a>
                        <a href="/wishlist"         class="btn btn--secondary">Ma wishlist</a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>
