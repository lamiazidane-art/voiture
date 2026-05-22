// dashboard.js 

document.addEventListener('DOMContentLoaded', () => {
    // Toggle sidebar sur mobile
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            toggleBtn.setAttribute('aria-expanded', sidebar.classList.contains('active'));
        });

        // Fermer le sidebar quand on clique sur un lien
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    // Confirmations sur les actions sensibles
    document.querySelectorAll('[data-confirm]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const message = link.getAttribute('data-confirm') || 'Confirmer cette action ?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});

