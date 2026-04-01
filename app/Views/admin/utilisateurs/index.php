<?php /** @var int $nbPilotes */ /** @var int $nbEtudiants */ ?>
<main class="container" id="main-content">
    <h1 class="page-title">Gestion des utilisateurs</h1>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem;">

        <!-- Pilotes -->
        <div class="card" style="padding:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;">
                <div>
                    <h2 style="font-size:1.1rem;font-weight:700;font-family:var(--font-head);margin-bottom:.25rem;">
                        🧑‍🏫 Pilotes
                    </h2>
                    <p style="font-size:2rem;font-weight:800;color:var(--primary);margin:0;">
                        <?= (int)$nbPilotes ?>
                    </p>
                </div>
                <a href="/admin/utilisateurs/creer?type=pilote"
                   class="btn btn--primary" style="font-size:.85rem;">
                    + Nouveau pilote
                </a>
            </div>
            <a href="/admin/pilotes" class="btn btn--secondary" style="width:100%;text-align:center;display:block;">
                Voir tous les pilotes →
            </a>
        </div>

        <!-- Étudiants -->
        <div class="card" style="padding:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;">
                <div>
                    <h2 style="font-size:1.1rem;font-weight:700;font-family:var(--font-head);margin-bottom:.25rem;">
                        🎓 Étudiants
                    </h2>
                    <p style="font-size:2rem;font-weight:800;color:#6366f1;margin:0;">
                        <?= (int)$nbEtudiants ?>
                    </p>
                </div>
                <a href="/admin/utilisateurs/creer?type=etudiant"
                   class="btn btn--primary" style="font-size:.85rem;">
                    + Nouvel étudiant
                </a>
            </div>
            <a href="/admin/etudiants" class="btn btn--secondary" style="width:100%;text-align:center;display:block;">
                Voir tous les étudiants →
            </a>
        </div>

    </div>
</main>
