<?php /** @var array $pilote */ ?>
<main class="container" id="main-content">

    <a href="/admin/pilotes" style="color:var(--text-muted);font-size:.9rem;">← Liste des pilotes</a>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-top:1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin:1.25rem 0 1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:800;font-family:var(--font-head);margin-bottom:.3rem;">
                🧑‍🏫 <?= htmlspecialchars(($pilote['prenom'] ?? '') . ' ' . ($pilote['nom'] ?? '')) ?>
            </h1>
            <p style="color:var(--text-muted);font-size:.9rem;">
                ✉️ <?= htmlspecialchars($pilote['Email'] ?? '') ?>
                <?php if (!empty($pilote['Telephone'])): ?>
                    &nbsp;|&nbsp; 📞 <?= htmlspecialchars($pilote['Telephone']) ?>
                <?php endif; ?>
                <?php if (!empty($pilote['date_creation'])): ?>
                    &nbsp;|&nbsp; 🗓 Créé le <?= date('d/m/Y', strtotime($pilote['date_creation'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="/admin/pilotes/<?= (int)$pilote['Id_pilote'] ?>/edit"
               class="btn btn--secondary" style="font-size:.85rem;">✏️ Modifier</a>
            <button type="button" class="btn btn--danger" style="font-size:.85rem;"
                    onclick="if(confirm('Supprimer ce pilote ?'))
                             document.getElementById('form-del').submit()">
                🗑 Supprimer
            </button>
            <form id="form-del" method="POST"
                  action="/admin/pilotes/<?= (int)$pilote['Id_pilote'] ?>/delete"
                  style="display:none;">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
        </div>
    </div>

    <!-- Promotions -->
    <div class="card" style="padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;font-family:var(--font-head);margin-bottom:1rem;">
            📚 Promotions gérées (<?= count($pilote['promotions'] ?? []) ?>)
        </h2>
        <?php if (empty($pilote['promotions'])): ?>
            <p style="color:var(--text-muted);font-size:.9rem;">Aucune promotion assignée.</p>
        <?php else: ?>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                <?php foreach ($pilote['promotions'] as $pr): ?>
                    <a href="/admin/promotions/<?= (int)$pr['Id_promotion'] ?>"
   style="text-decoration:none;">
    <span class="tag" style="cursor:pointer;">
        <?= htmlspecialchars($pr['Libelle']) ?> — <?= htmlspecialchars($pr['Annee']) ?>
        (<?= htmlspecialchars($pr['Filiere'] ?? '') ?>)
    </span>
</a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
