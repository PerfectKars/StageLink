<section class="hero">
    <div class="container">
        <h1>Trouvez votre stage idéal</h1>
        <p class="hero__sub">
            <?= $nbOffres ?> offres disponibles dans <?= $nbEntreprises ?> entreprises partenaires.
        </p>
        <a href="/offres" class="btn btn--primary btn--lg">Parcourir les offres</a>
    </div>
</section>

<section class="section" style="background:#f9fafb;">
  <div class="container">
    <h2>À propos de nous</h2></br>
    <p>
      Notre plateforme est dédiée à la recherche de stages pour les étudiants et jeunes diplômés. Nous mettons en relation
      des candidats motivés avec des entreprises partenaires dans de nombreux secteurs d’activité.
    </p>
    <p>
      Grâce à une interface simple et intuitive, vous pouvez parcourir des offres actuelles, filtrer selon vos critères
      (ville, durée, rémunération) et postuler facilement.
    </p>
    <p>
      Notre objectif est de faciliter votre insertion professionnelle en vous aidant à trouver le stage qui correspond
      à votre profil et à vos ambitions.
    </p>
  </div>
</section>

<section class="stats">

<div class="container">
        
        <div class="carte-grid_1">
            
    <div class="card">
        <h3>🏢 <?= $nbEntreprises ?> entreprises</h3></br>
        <p>Plus de <?= $nbEntreprises ?> entreprises partenaires nous font confiance pour proposer des stages de qualité aux étudiants.</p>
</div>

<div class="card">
    <h3>🎓 <?= $nbOffres ?> stages</h3></br>
    <p>
        Découvrez <?= $nbOffres ?> offres de stage disponibles dans divers domaines 
        et trouvez celle qui correspond à votre profil.
    </p>
</div>

            <div class="card">
                <h3>💻 Simple & rapide</h3></br>
                <p>Trouvez facilement le stage qui vous correspond grâce à une plateforme claire et intuitive.En quelques clics, accédez aux meilleures opportunités près de chez vous.</p>
            </div>

        </div>

    </div>
</section>

<section class="section">
    <div class="container">
        <h2>📝 Dernières offres publiées</h2></br>
        <div class="cards-grid">
            <?php foreach ($dernieresOffres as $offre): ?>
    <article class="card">
        <h3 class="card__title">
            <a href="/offres/<?= (int) $offre['Id_offre'] ?>">
                <?= htmlspecialchars($offre['Titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </a>
        </h3>
        <p class="card__meta">
            <?= htmlspecialchars($offre['Nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p class="card__meta">
            <?= htmlspecialchars($offre['Ville'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p class="card__meta">
            <?= number_format((float) ($offre['Base_remuneration'] ?? 0), 2) ?> €/h
        </p>
    </article>
<?php endforeach; ?>
        </div><br>
        <div class="text-center">
            <a href="/offres" class="btn btn--outline">Voir toutes les offres</a>
        </div>
    </div>
</section>
