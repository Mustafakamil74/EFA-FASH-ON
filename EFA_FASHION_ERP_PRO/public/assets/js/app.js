/* EFA FASHION ERP PRO — front-end behaviour */
(function () {
    'use strict';

    // --- Theme toggle (persisted in a cookie + sent to server) ---
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const html = document.documentElement;
            const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', next);
            document.cookie = 'theme=' + next + ';path=/;max-age=' + (60 * 60 * 24 * 365);
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
            }
        });
    }

    // --- Sidebar collapse / mobile toggle ---
    const sidebarToggle = document.getElementById('sidebarToggle');
    const shell = document.querySelector('.app-shell');
    if (sidebarToggle && shell) {
        sidebarToggle.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
                shell.classList.toggle('mobile-open');
            } else {
                shell.classList.toggle('collapsed');
            }
        });
    }

    // --- Confirm before destructive actions ---
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.matches('[data-confirm]')) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        }
    });

    // --- Auto-dismiss flash alerts after 5s ---
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(el).close();
            }
        }, 5000);
    });
})();
