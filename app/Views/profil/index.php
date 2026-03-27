<section class="section">
    <div class="container">
        <h1 style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;margin-bottom:1.5rem;">
            Mon profil
        </h1>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;">

            <!-- Infos personnelles -->
            <div class="card">
                <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:1.25rem;">
                    Informations personnelles
                </h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="/profil" class="form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

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
                        <label>Nouveau mot de passe <span style="color:var(--text-muted);font-size:.8rem">(laisser vide pour ne pas changer)</span></label>
                        <input type="password" name="mot_de_passe" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn--primary btn--full">Sauvegarder</button>
                </form>
            </div>

            <!-- Candidatures -->
            <div>
                <?php if ($_SESSION['user']['role'] === 'etudiant'): ?>
                <div class="card mb-2">
                    <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:1rem;">
                        Mes candidatures
                        <span style="background:var(--primary);color:#fff;border-radius:99px;padding:.15rem .6rem;font-size:.8rem;margin-left:.5rem;">
                            <?= count($candidatures) ?>
                        </span>
                    </h2>

                    <?php if (empty($candidatures)): ?>
                        <p class="empty-state" style="padding:1.5rem 0;">Aucune candidature pour le moment.</p>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:.75rem;">
                            <?php foreach ($candidatures as $c): ?>
                                <div style="padding:.85rem;background:var(--bg);border-radius:var(--radius-sm);display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <p style="font-weight:600;font-size:.95rem;">
                                            <?= htmlspecialchars($c['Titre'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p style="font-size:.82rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($c['entreprise'], ENT_QUOTES, 'UTF-8') ?> —
                                            <?= date('d/m/Y', strtotime($c['Date_candidature'])) ?>
                                        </p>
                                    </div>
                                    <?php
                                    $badgeClass = match($c['Statut']) {
                                        'Accepté'   => 'badge--green',
                                        'Refusé'    => 'badge--red',
                                        'Entretien' => 'badge--orange',
                                        default     => ''
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($c['Statut'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Rôle -->
                <div class="card">
                    <h2 style="font-family:var(--font-head);font-size:1rem;font-weight:700;margin-bottom:.75rem;">
                        Mon compte
                    </h2>
                    <p style="font-size:.9rem;color:var(--text-muted);">Rôle :
                        <span class="tag"><?= htmlspecialchars($_SESSION['user']['role'], ENT_QUOTES, 'UTF-8') ?></span>
                    </p>

                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
                        <a href="/admin/etudiants" class="btn btn--secondary">Gérer les étudiants</a>
                        <a href="/admin/pilotes" class="btn btn--secondary">Gérer les pilotes</a>
                        <a href="/offres/create" class="btn btn--secondary">Créer une offre</a>
                        <a href="/entreprises/create" class="btn btn--secondary">Créer une entreprise</a>
                    </div>
                    <?php endif; ?>

                    <?php if ($_SESSION['user']['role'] === 'pilote'): ?>
                    <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
                        <a href="/offres/create" class="btn btn--secondary">Créer une offre</a>
                        <a href="/entreprises/create" class="btn btn--secondary">Créer une entreprise</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
