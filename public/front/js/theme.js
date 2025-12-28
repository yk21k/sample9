(function () {
    const body = document.body;
    const toggle = document.getElementById('themeToggle');

    if (!toggle) return;

    toggle.addEventListener('click', function () {
        const isDark = body.classList.contains('theme-dark');

        body.classList.toggle('theme-dark', !isDark);
        body.classList.toggle('theme-light', isDark);

        document.cookie =
            'theme=' + (isDark ? 'light' : 'dark') + '; path=/; max-age=31536000';
    });
})();
