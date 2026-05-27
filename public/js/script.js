document.addEventListener('DOMContentLoaded', function () {
    var THEME_KEY = 'modastyle-theme';
    var root = document.documentElement;
    var themeToggle = document.querySelector('[data-theme-toggle]');
    var themeIcon = document.querySelector('[data-theme-icon]');
    var prefersDarkScheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');

    function applyTheme(theme) {
        var resolvedTheme = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', resolvedTheme);
        root.setAttribute('data-bs-theme', resolvedTheme);

        if (themeIcon) {
            themeIcon.classList.toggle('fa-moon', resolvedTheme === 'light');
            themeIcon.classList.toggle('fa-sun', resolvedTheme === 'dark');
        }
    }

    function resolveInitialTheme() {
        var savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme === 'light' || savedTheme === 'dark') {
            return savedTheme;
        }

        // По умолчанию стартуем со светлой темы.
        // Тёмная тема включается только вручную и сохраняется в localStorage.
        return 'light';
    }

    function initThemeToggle() {
        var currentTheme = resolveInitialTheme();
        applyTheme(currentTheme);

        if (!themeToggle) {
            return;
        }

        themeToggle.addEventListener('click', function () {
            var nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            localStorage.setItem(THEME_KEY, nextTheme);
        });
    }

    initThemeToggle();

    document.querySelectorAll('.minus-btn').forEach(function (btn) {
        if (btn.closest('.cart-qty-form')) {
            return;
        }

        btn.addEventListener('click', function () {
            var input = this.parentElement.querySelector('.quantity-input');
            var val = parseInt(input.value, 10) || 1;
            if (val > 1) {
                input.value = val - 1;
            }
        });
    });

    document.querySelectorAll('.plus-btn').forEach(function (btn) {
        if (btn.closest('.cart-qty-form')) {
            return;
        }

        btn.addEventListener('click', function () {
            var input = this.parentElement.querySelector('.quantity-input');
            var val = parseInt(input.value, 10) || 1;
            var max = parseInt(input.getAttribute('max'), 10) || 100;
            if (val < max) {
                input.value = val + 1;
            }
        });
    });
});
