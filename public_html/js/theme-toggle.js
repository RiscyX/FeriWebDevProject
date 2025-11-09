document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return; // no button → exit

    const html  = document.documentElement;
    const saved = localStorage.getItem('theme');
    if (saved) html.setAttribute('data-bs-theme', saved);

    toggle.addEventListener('click', () => {
        const curr = html.getAttribute('data-bs-theme');
        const next = curr === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
    });
});
