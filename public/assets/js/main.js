// ================================================
// main.js - Script global StageLink
// ================================================


// ── Menu burger ───────────────────────────────────────────────────────────────
const burger = document.querySelector('.navbar__burger');
const menu   = document.querySelector('.navbar__menu');

if (burger && menu) {
    burger.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        burger.setAttribute('aria-expanded', isOpen);
    });

    // Fermer le menu si on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!burger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
        }
    });
}

// ── Section : Entreprise - Formulaire (création / modification) ─────────────────
if (document.getElementById('form-entreprise')) {
    
    // Variables principales du formulaire entreprise
    const inputAdresse = document.getElementById('Adresse');
    const inputCP      = document.getElementById('Code_postal');
    const inputVille   = document.getElementById('Ville');
    const suggestions  = document.getElementById('adresse-suggestions');

    let timer;

    // Autocomplétion adresse principale (siège social)
    if (inputAdresse && suggestions) {
        inputAdresse.addEventListener('input', () => {
            clearTimeout(timer);
            const q = inputAdresse.value.trim();
            if (q.length < 3) {
                suggestions.style.display = 'none';
                return;
            }

            timer = setTimeout(async () => {
                try {
                    const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=6&type=housenumber`);
                    const data = await res.json();
                    renderSuggestions(data.features || []);
                } catch (e) {
                    suggestions.style.display = 'none';
                }
            }, 300);
        });

        function renderSuggestions(features) {
            suggestions.innerHTML = '';
            if (!features.length) {
                suggestions.style.display = 'none';
                return;
            }

            features.forEach(f => {
                const p = f.properties;
                const li = document.createElement('li');
                li.textContent = p.label;
                li.style.cssText = 'padding:.6rem 1rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid var(--border);';

                li.addEventListener('mouseenter', () => li.style.background = 'var(--surface)');
                li.addEventListener('mouseleave', () => li.style.background = '');
                
                li.addEventListener('click', () => {
                    inputAdresse.value = p.name || p.label;
                    inputCP.value      = p.postcode || '';
                    inputVille.value   = p.city || '';
                    suggestions.style.display = 'none';
                });

                suggestions.appendChild(li);
            });
            suggestions.style.display = 'block';
        }

        // Fermer les suggestions en cliquant ailleurs
        document.addEventListener('click', e => {
            if (!inputAdresse.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.style.display = 'none';
            }
        });
    }

    // Validation SIRET côté client
    const formEntreprise = document.getElementById('form-entreprise');
    if (formEntreprise) {
        formEntreprise.addEventListener('submit', e => {
            const siretInput = document.getElementById('SIRET');
            if (siretInput) {
                const siret = siretInput.value.replace(/\D/g, '');
                if (siret.length !== 14) {
                    e.preventDefault();
                    alert('Le SIRET doit contenir exactement 14 chiffres.');
                    siretInput.focus();
                }
            }
        });
    }

    // Fonction réutilisable d'autocomplétion pour les sites supplémentaires
    function attachAutocomplete(input, sugg, cp, ville) {
        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 3) {
                sugg.style.display = 'none';
                return;
            }

            timer = setTimeout(async () => {
                try {
                    const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=5&type=housenumber`);
                    const data = await res.json();
                    sugg.innerHTML = '';

                    if (!data.features.length) {
                        sugg.style.display = 'none';
                        return;
                    }

                    data.features.forEach(f => {
                        const p = f.properties;
                        const li = document.createElement('li');
                        li.textContent = p.label;
                        li.style.cssText = 'padding:.6rem 1rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid var(--border);';

                        li.addEventListener('mouseenter', () => li.style.background = 'var(--surface)');
                        li.addEventListener('mouseleave', () => li.style.background = '');

                        li.addEventListener('click', () => {
                            input.value = p.name || p.label;
                            cp.value    = p.postcode || '';
                            ville.value = p.city || '';
                            sugg.style.display = 'none';
                        });

                        sugg.appendChild(li);
                    });
                    sugg.style.display = 'block';
                } catch (e) {
                    sugg.style.display = 'none';
                }
            }, 300);
        });

        // Fermer suggestions en cliquant ailleurs
        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !sugg.contains(e.target)) {
                sugg.style.display = 'none';
            }
        });
    }

    // ── Gestion des sites supplémentaires ─────────────────────────────────────
    let siteCount = 0;

    window.ajouterSite = function() {   // window. pour pouvoir l'appeler depuis onclick
        siteCount++;

        const div = document.createElement('div');
        div.id = `site-${siteCount}`;
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
                    style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid var(--border);
                           border-radius:8px;list-style:none;margin:0;padding:.25rem 0;width:100%;
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

        // Attacher l'autocomplétion sur le nouveau site
        const input = div.querySelector('.site-adresse');
        const sugg  = div.querySelector('.site-suggestions');
        const cp    = div.querySelector('.site-cp');
        const ville = div.querySelector('.site-ville');

        if (input && sugg && cp && ville) {
            attachAutocomplete(input, sugg, cp, ville);
        }
    };
}

if (document.getElementById('form-delete') || document.getElementById('cv-existant')) {

    // === Confirmation de suppression d'entreprise ===
    window.confirmerSuppression = function() {
        const nbOffres = parseInt(document.getElementById('nb-offres-hidden')?.value ?? 0);
        const nomEntreprise = document.getElementById('nom-entreprise-hidden')?.value ?? 'cette entreprise';

        let msg = `Supprimer "${nomEntreprise}" ?`;

        if (nbOffres > 0) {
            msg += `\n\n⚠️ ${nbOffres} offre(s) rattachée(s) seront également supprimée(s).`;
        }

        if (confirm(msg)) {
            const formDelete = document.getElementById('form-delete');
            if (formDelete) formDelete.submit();
        }
    };

    // === Gestion CV existant (formulaire de postulation) ===
    const selectCv = document.getElementById('cv-existant');
    const inputCv  = document.getElementById('cv');

    if (selectCv && inputCv) {
        selectCv.addEventListener('change', () => {
            inputCv.required = (selectCv.value === '0' || selectCv.value === '');
        });

        // Initialisation
        inputCv.required = (selectCv.value === '0' || selectCv.value === '');
    }
}

// ─────────────────────────────────────────────────────────────
// SECTION : OFFRE - Statistiques (offre/statistiques.php)
// ─────────────────────────────────────────────────────────────

if (document.querySelector('.carousel')) {

    const statsData = window.statsData || { 
        total: 0, 
        actives: 0, 
        companies: [], 
        skills: [] 
    };

    let chartCompanies = null;
    let chartSkills = null;

    // Graphique Entreprises (Line)
    function createCompaniesChart() {
        const ctx = document.getElementById('chartCompanies')?.getContext('2d');
        if (!ctx || statsData.companies.length === 0) return;

        chartCompanies = new Chart(ctx, {
            type: 'line',
            data: {
                labels: statsData.companies.map(c => c.Nom),
                datasets: [{
                    label: 'Nombre d\'offres',
                    data: statsData.companies.map(c => c.nb),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false } 
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'var(--border)' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { 
                            maxRotation: 45,
                            minRotation: 45,
                            color: 'var(--text-muted)'
                        }
                    }
                }
            }
        });
    }

    // Graphique Compétences (Line)
    function createSkillsChart() {
        const ctx = document.getElementById('chartSkills')?.getContext('2d');
        if (!ctx || statsData.skills.length === 0) return;

        chartSkills = new Chart(ctx, {
            type: 'line',
            data: {
                labels: statsData.skills.map(s => s.Libelle),
                datasets: [{
                    label: 'Nombre de demandes',
                    data: statsData.skills.map(s => s.nb),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false } 
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'var(--border)' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { 
                            maxRotation: 45,
                            minRotation: 45,
                            color: 'var(--text-muted)'
                        }
                    }
                }
            }
        });
    }

    // Gestion Carousel + activation visuelle
    function initCarousel() {
        const navBtns = document.querySelectorAll('.carousel__nav-btn');
        const slides  = document.querySelectorAll('.carousel__slide');

        const showSlide = (index) => {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.style.display = (i === 0) ? 'grid' : 'block';
                    setTimeout(() => { slide.style.opacity = '1'; }, 10);
                } else {
                    slide.style.opacity = '0';
                    setTimeout(() => { slide.style.display = 'none'; }, 300);
                }
            });

            // Mise à jour style des boutons (onglet actif en bleu)
            navBtns.forEach((btn, i) => {
                if (i === index) {
                    btn.classList.add('carousel__nav-btn--active');
                    btn.style.background = 'var(--primary)';
                    btn.style.color = '#fff';
                } else {
                    btn.classList.remove('carousel__nav-btn--active');
                    btn.style.background = 'var(--border)';
                    btn.style.color = 'var(--text)';
                }
            });

            // Créer les graphiques seulement quand leur slide est visible
            if (index === 1 && !chartCompanies) setTimeout(createCompaniesChart, 120);
            if (index === 2 && !chartSkills)    setTimeout(createSkillsChart, 120);

            // Resize si déjà créé
            if (index === 1 && chartCompanies) setTimeout(() => chartCompanies?.resize(), 150);
            if (index === 2 && chartSkills)    setTimeout(() => chartSkills?.resize(), 150);
        };

        navBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                showSlide(parseInt(btn.getAttribute('data-slide')));
            });
        });

        // Initialisation
        showSlide(0);

        // Graphique du premier slide (toujours visible)
        setTimeout(() => {
            const ctx = document.getElementById('chartOverview')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Total', 'Actives', 'Inactives'],
                        datasets: [{
                            label: 'Offres',
                            data: [statsData.total, statsData.actives, statsData.total - statsData.actives],
                            backgroundColor: ['#3b82f6', '#10b981', '#6366f1'],
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }
        }, 100);
    }

    // Lancement
    document.addEventListener('DOMContentLoaded', initCarousel);

    console.log('✅ Statistiques chargées - Line charts restaurés');
}


// ─────────────────────────────────────────────────────────────
// SECTION : OFFRE - Formulaire (création / modification)
// ─────────────────────────────────────────────────────────────

if (document.getElementById('Id_entreprise') && document.getElementById('Id_site')) {

    console.log('📝 Initialisation formulaire offre...');

    const formData = window.offreFormData || { 
        sitesParEntreprise: {}, 
        currentSite: 0 
    };

    const selectEnt  = document.getElementById('Id_entreprise');
    const selectSite = document.getElementById('Id_site');
    const siteHint   = document.getElementById('site-hint');

    function updateSites(idEntreprise, preselectId = 0) {
        if (!idEntreprise) {
            selectSite.innerHTML = '<option value="">-- Sélectionner d\'abord une entreprise --</option>';
            if (siteHint) siteHint.style.display = 'none';
            return;
        }

        // Conversion sécurisée en string (car clés JSON sont des strings)
        const key = String(idEntreprise).trim();
        let sites = formData.sitesParEntreprise[key];

        // Fallback si la clé est numérique
        if (!sites && formData.sitesParEntreprise[idEntreprise]) {
            sites = formData.sitesParEntreprise[idEntreprise];
        }

        selectSite.innerHTML = '<option value="">-- Sélectionner un site --</option>';

        if (!sites || sites.length === 0) {
            selectSite.innerHTML = '<option value="">Aucun site trouvé pour cette entreprise</option>';
            if (siteHint) siteHint.style.display = 'none';
            return;
        }

        sites.forEach(site => {
            const opt = document.createElement('option');
            opt.value = site.Id_site;
            opt.textContent = `${site.Adresse}, ${site.Code_postal} ${site.Ville} (${site.Pays || 'France'})`;
            
            if (parseInt(site.Id_site) === parseInt(preselectId)) {
                opt.selected = true;
            }
            selectSite.appendChild(opt);
        });

        if (siteHint) siteHint.style.display = 'block';
    }

    // Événement changement entreprise
    selectEnt.addEventListener('change', () => {
        updateSites(selectEnt.value);
    });

    // Initialisation au chargement de la page
    if (selectEnt.value) {
        updateSites(selectEnt.value, formData.currentSite);
    }

    // ======================
    // Validation temps réel : Durée + Gratification
    // ======================
    const inputDuree = document.getElementById('duree_mois');
    const inputGrat  = document.getElementById('Base_remuneration');

    if (inputDuree && inputGrat) {
        const hintGrat = document.createElement('p');
        hintGrat.style.cssText = 'font-size:0.85rem; margin-top:0.25rem;';
        inputGrat.parentNode.appendChild(hintGrat);

        function checkGratification() {
            const duree = parseInt(inputDuree.value) || 0;
            const grat  = parseFloat(inputGrat.value) || 0;

            // Durée > 6 mois
            if (duree > 6) {
                inputDuree.setCustomValidity('La durée maximale d’un stage est de 6 mois.');
                inputDuree.reportValidity();
            } else {
                inputDuree.setCustomValidity('');
            }

            // Gratification légale
            if (duree > 2 && grat < 4.50) {
                hintGrat.textContent = '⚠️ Gratification minimale légale : 4,50 €/h pour un stage > 2 mois.';
                hintGrat.style.color = '#dc2626';
                inputGrat.setCustomValidity('Minimum 4,50 €/h pour un stage de plus de 2 mois.');
            } else {
                hintGrat.textContent = (duree > 0 && duree <= 2)
                    ? 'ℹ️ Stage ≤ 2 mois : gratification non obligatoire.'
                    : '';
                hintGrat.style.color = 'var(--text-muted)';
                inputGrat.setCustomValidity('');
            }
        }

        inputDuree.addEventListener('input', checkGratification);
        inputGrat.addEventListener('input', checkGratification);
        checkGratification(); // Initial
    }

    console.log('✅ Formulaire offre initialisé avec sites dynamiques');
}


// ─────────────────────────────────────────────────────────────
// SECTION : OFFRE - Index (liste des offres avec recherche)
// ─────────────────────────────────────────────────────────────

if (document.getElementById('input-ville') || document.getElementById('btn-voir-plus-comps')) {

    console.log('🔍 Initialisation page liste des offres...');

    // ======================
    // Autocomplétion Ville
    // ======================
    const inputVille = document.getElementById('input-ville');
    const villeSugg  = document.getElementById('ville-suggestions');
    let villeTimer;

    if (inputVille && villeSugg) {
        inputVille.addEventListener('input', () => {
            clearTimeout(villeTimer);
            const q = inputVille.value.trim();
            if (q.length < 2) {
                villeSugg.style.display = 'none';
                return;
            }

            villeTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=6&type=municipality`);
                    const data = await res.json();

                    villeSugg.innerHTML = '';
                    if (!data.features || !data.features.length) {
                        villeSugg.style.display = 'none';
                        return;
                    }

                    data.features.forEach(f => {
                        const p = f.properties;
                        const li = document.createElement('li');
                        li.textContent = `${p.city} (${p.postcode || ''})`;
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
                } catch (e) {
                    villeSugg.style.display = 'none';
                }
            }, 300);
        });

        document.addEventListener('click', e => {
            if (!inputVille.contains(e.target) && !villeSugg.contains(e.target)) {
                villeSugg.style.display = 'none';
            }
        });
    }

    // ======================
    // Toggle "Voir plus" Compétences
    // ======================
    const btnVoirPlus = document.getElementById('btn-voir-plus-comps');
    if (btnVoirPlus) {
        console.log('✅ Bouton Voir plus trouvé');

        btnVoirPlus.addEventListener('click', function () {
            const hiddenContainer = document.getElementById('comps-cachees');
            if (!hiddenContainer) return;

            const isHidden = hiddenContainer.style.display === 'none' || hiddenContainer.style.display === '';

            hiddenContainer.style.display = isHidden ? 'contents' : 'none';
            this.textContent = isHidden ? 'Voir moins' : 'Voir plus';
            this.classList.toggle('ouvert', isHidden);

            console.log('Toggle compétences :', isHidden ? 'ouvert' : 'fermé');
        });
    } else {
        console.warn('❌ Bouton #btn-voir-plus-comps non trouvé');
    }

    console.log('✅ Page liste des offres initialisée');
}

// ─────────────────────────────────────────────────────────────
// SECTION : OFFRE - Show (détail d'une offre)
// ─────────────────────────────────────────────────────────────

if (document.querySelector('.offre-detail')) {

    console.log('📄 Initialisation page détail offre...');

    const data = window.offreShowData || { idOffre: 0, titre: '', nbCandidatures: 0 };

    // ======================
    // Confirmation suppression (Admin / Pilote)
    // ======================
    window.confirmerSuppressionOffre = function() {
        let msg = `Supprimer l'offre "${data.titre}" ?`;

        if (data.nbCandidatures > 0) {
            msg += `\n\n⚠️ ${data.nbCandidatures} candidature(s) associée(s) seront également supprimées.`;
        }

        if (confirm(msg)) {
            const formDelete = document.getElementById('form-delete-offre');
            if (formDelete) formDelete.submit();
        }
    };

    // ======================
    // Toggle "Voir plus" pour les compétences cachées
    // ======================
    const voirPlusBtn = document.getElementById('voirPlusBtn');
    if (voirPlusBtn) {
        voirPlusBtn.addEventListener('click', function () {
            const hiddenDiv = document.getElementById('competencesCachees');
            if (!hiddenDiv) return;

            const isHidden = hiddenDiv.style.display === 'none' || hiddenDiv.style.display === '';

            hiddenDiv.style.display = isHidden ? 'block' : 'none';
            this.textContent = isHidden ? 'Voir moins' : 'Voir plus';
            this.classList.toggle('ouvert', isHidden);
        });
    }

    console.log('✅ Page détail offre initialisée');
}