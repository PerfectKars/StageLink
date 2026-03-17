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
