import './bootstrap';
import './ai-chat-widget';
import './users-management';

function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const root = document.documentElement;
            const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
            root.classList.toggle('dark', nextTheme === 'dark');
            localStorage.setItem('theme', nextTheme);
        });
    });
}

function initFlashBanner() {
    const banner = document.getElementById('flash-banner');
    const closeBtn = banner?.querySelector('[data-flash-close]');
    if (!banner || !closeBtn) return;

    const close = () => banner.remove();
    closeBtn.addEventListener('click', close);

    window.setTimeout(close, 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initFlashBanner();
});
