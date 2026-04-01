<?php
$isEdit    = isset($entreprise['Id_entreprise']);
$actionUrl = $isEdit ? '/entreprises/' . $entreprise['Id_entreprise'] . '/edit' : '/entreprises/create';
$errors    = $errors ?? [];
$statuts   = ['SAS','SARL','SA','SNC','EURL','EI','Auto-entrepreneur','Association (loi 1901)','Établissement public','Autre'];
?>
<main class="container" id="main-content">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <section class="form-card">
        <h1 class="form-title"><?= $isEdit ? "Modifier l'entreprise" : 'Ajouter une entreprise' ?></h1>
        <form method="POST" action="<?= htmlspecialchars($actionUrl) ?>" novalidate id="form-entreprise">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <h2 class="form-section-title">Informations légales</h2>

            <div class="form-group">
                <label for="Nom" class="form-label">Raison sociale <span>*</span></label>
                <input type="text" id="Nom" name="Nom" class="form-input" required maxlength="150"
                       value="<?= htmlspecialchars($entreprise['Nom'] ?? '') ?>" placeholder="Ex : CESI SAS">
            </div>

            <div class="form-group">
                <label for="SIRET" class="form-label">N° SIRET <span>*</span>
                    <small style="font-weight:normal;color:var(--text-muted);">— 14 chiffres</small>
                </label>
                <input type="text" id="SIRET" name="SIRET" class="form-input" required
                       maxlength="14" pattern="\d{14}" inputmode="numeric"
                       value="<?= htmlspecialchars($entreprise['SIRET'] ?? '') ?>"
                       placeholder="12345678901234"
                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,14);document.getElementById('siret-count').textContent=this.value.length+'/14 chiffres'">
                <small id="siret-count" style="color:var(--text-muted);"><?= strlen($entreprise['SIRET'] ?? '') ?>/14 chiffres</small>
            </div>

            <div class="form-group">
                <label for="statut_juridique" class="form-label">Statut juridique <span>*</span></label>
                <select id="statut_juridique" name="statut_juridique" class="form-input" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($statuts as $s): ?>
                        <option value="<?= $s ?>" <?= ($entreprise['statut_juridique'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h2 class="form-section-title" style="margin-top:1.5rem;">Siège social / Adresse principale
                <small style="font-weight:normal;font-size:.85rem;color:var(--text-muted);">— lieu juridique de l'entreprise</small>
            </h2>

            <div class="form-group" style="position:relative;">
                <label for="Adresse" class="form-label">Adresse <span>*</span></label>
                <input type="text" id="Adresse" name="Adresse" class="form-input" required autocomplete="off"
                       value="<?= htmlspecialchars($entreprise['Adresse'] ?? '') ?>"
                       placeholder="Commencez à taper une adresse…">
                <ul id="adresse-suggestions"
                    style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid var(--border);
                           border-radius:8px;list-style:none;margin:0;padding:.25rem 0;width:100%;
                           box-shadow:0 4px 12px rgba(0,0,0,.1);max-height:220px;overflow-y:auto;"></ul>
            </div>

            <div style="display:grid;grid-template-columns:140px 1fr;gap:1rem;">
                <div class="form-group">
                    <label for="Code_postal" class="form-label">Code postal <span>*</span></label>
                    <input type="text" id="Code_postal" name="Code_postal" class="form-input" required
                           maxlength="10" inputmode="numeric"
                           value="<?= htmlspecialchars($entreprise['Code_postal'] ?? '') ?>" placeholder="75001">
                </div>
                <div class="form-group">
                    <label for="Ville" class="form-label">Ville <span>*</span></label>
                    <input type="text" id="Ville" name="Ville" class="form-input" required
                           value="<?= htmlspecialchars($entreprise['Ville'] ?? '') ?>" placeholder="Paris">
                </div>
            </div>

            <div class="form-group">
                <label for="Pays" class="form-label">Pays</label>
                <input type="text" id="Pays" name="Pays" class="form-input"
                       value="<?= htmlspecialchars($entreprise['Pays'] ?? 'France') ?>">
            </div>

            <!-- Sites supplémentaires -->
<div id="sites-supplementaires"></div>

<button type="button" id="btn-ajouter-site"
        style="margin-bottom:1.5rem;background:none;border:1px dashed var(--border);
               border-radius:8px;padding:.6rem 1.2rem;cursor:pointer;
               color:var(--text-muted);font-size:.9rem;width:100%;"
        onclick="ajouterSite()">
    + Ajouter un site supplémentaire
</button>

            <h2 class="form-section-title" style="margin-top:1.5rem;">Contact</h2>

            <div class="form-group">
                <label for="Email_contact" class="form-label">Email de contact</label>
                <input type="email" id="Email_contact" name="Email_contact" class="form-input" required
                       value="<?= htmlspecialchars($entreprise['Email_contact'] ?? '') ?>"
                       placeholder="contact@entreprise.fr">
            </div>

            <div class="form-group">
                <label for="Tel_contact" class="form-label">Téléphone
                    <small style="font-weight:normal;color:var(--text-muted);">— 10 chiffres</small>
                </label>
                <input type="tel" id="Tel_contact" name="Tel_contact" class="form-input"
                       maxlength="10"
                       value="<?= htmlspecialchars($entreprise['Tel_contact'] ?? '') ?>"
                       placeholder="0123456789"
                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
            </div>

            <div class="form-group">
                <label for="Description" class="form-label">Description</label>
                <textarea id="Description" name="Description" class="form-input" rows="4"
                          placeholder="Activité, secteur, culture d'entreprise…"
                ><?= htmlspecialchars($entreprise['Description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/entreprises" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer' : "Ajouter l'entreprise" ?>
                </button>
            </div>
        </form>
    </section>
</main>

<script>
// Autocomplétion adresse — API adresse.data.gouv.fr
const inputAdresse = document.getElementById('Adresse');
const inputCP      = document.getElementById('Code_postal');
const inputVille   = document.getElementById('Ville');
const suggestions  = document.getElementById('adresse-suggestions');
let timer;

inputAdresse.addEventListener('input', () => {
    clearTimeout(timer);
    const q = inputAdresse.value.trim();
    if (q.length < 3) { suggestions.style.display = 'none'; return; }
    timer = setTimeout(async () => {
        try {
            const res  = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=6&type=housenumber`);
            const data = await res.json();
            renderSuggestions(data.features || []);
        } catch { suggestions.style.display = 'none'; }
    }, 300);
});

function renderSuggestions(features) {
    suggestions.innerHTML = '';
    if (!features.length) { suggestions.style.display = 'none'; return; }
    features.forEach(f => {
        const p  = f.properties;
        const li = document.createElement('li');
        li.textContent = p.label;
        li.style.cssText = 'padding:.6rem 1rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid var(--border);';
        li.addEventListener('mouseenter', () => li.style.background = 'var(--surface)');
        li.addEventListener('mouseleave', () => li.style.background = '');
        li.addEventListener('click', () => {
            inputAdresse.value = p.name;
            inputCP.value      = p.postcode || '';
            inputVille.value   = p.city    || '';
            suggestions.style.display = 'none';
        });
        suggestions.appendChild(li);
    });
    suggestions.style.display = 'block';
}

document.addEventListener('click', e => {
    if (!inputAdresse.contains(e.target) && !suggestions.contains(e.target))
        suggestions.style.display = 'none';
});

// Validation SIRET front
document.getElementById('form-entreprise').addEventListener('submit', e => {
    const siret = document.getElementById('SIRET').value;
    if (siret.length !== 14) {
        e.preventDefault();
        alert('Le SIRET doit contenir exactement 14 chiffres.');
        document.getElementById('SIRET').focus();
    }
});
</script>

<script>
let siteCount = 0;

function ajouterSite() {
    siteCount++;
    const div = document.createElement('div');
    div.id = 'site-' + siteCount;
    div.style.cssText = 'border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1rem;';
    div.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
            <strong style="font-size:.9rem;">📍 Site ${siteCount + 1}</strong>
            <button type="button" onclick="document.getElementById('site-${siteCount}').remove()"
                    style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;">✕</button>
        </div>
        <div class="form-group" style="position:relative;">
            <label class="form-label">Adresse</label>
            <input type="text" name="sites[${siteCount}][Adresse]" class="form-input site-adresse"
                   autocomplete="off" placeholder="Commencez à taper…">
            <ul class="site-suggestions"
                style="display:none;position:absolute;z-index:100;background:#fff;
                       border:1px solid var(--border);border-radius:8px;list-style:none;
                       margin:0;padding:.25rem 0;width:100%;
                       box-shadow:0 4px 12px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;"></ul>
        </div>
        <div style="display:grid;grid-template-columns:140px 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Code postal</label>
                <input type="text" name="sites[${siteCount}][Code_postal]"
                       class="form-input site-cp" maxlength="10" placeholder="75001">
            </div>
            <div class="form-group">
                <label class="form-label">Ville</label>
                <input type="text" name="sites[${siteCount}][Ville]"
                       class="form-input site-ville" placeholder="Paris">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Pays</label>
            <input type="text" name="sites[${siteCount}][Pays]"
                   class="form-input" value="France">
        </div>
    `;
    document.getElementById('sites-supplementaires').appendChild(div);

    // Autocomplétion sur le nouveau champ
    const input = div.querySelector('.site-adresse');
    const sugg  = div.querySelector('.site-suggestions');
    const cp    = div.querySelector('.site-cp');
    const ville = div.querySelector('.site-ville');
    attachAutocomplete(input, sugg, cp, ville);
}

function attachAutocomplete(input, sugg, cp, ville) {
    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 3) { sugg.style.display = 'none'; return; }
        timer = setTimeout(async () => {
            try {
                const res  = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=5&type=housenumber`);
                const data = await res.json();
                sugg.innerHTML = '';
                if (!data.features.length) { sugg.style.display = 'none'; return; }
                data.features.forEach(f => {
                    const p  = f.properties;
                    const li = document.createElement('li');
                    li.textContent = p.label;
                    li.style.cssText = 'padding:.6rem 1rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid var(--border);';
                    li.addEventListener('mouseenter', () => li.style.background = 'var(--surface)');
                    li.addEventListener('mouseleave', () => li.style.background = '');
                    li.addEventListener('click', () => {
                        input.value = p.name;
                        cp.value    = p.postcode || '';
                        ville.value = p.city    || '';
                        sugg.style.display = 'none';
                    });
                    sugg.appendChild(li);
                });
                sugg.style.display = 'block';
            } catch { sugg.style.display = 'none'; }
        }, 300);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !sugg.contains(e.target))
            sugg.style.display = 'none';
    });
}
</script>