const STORAGE_KEY = 'agrolens-theme';

export function getStoredTheme() {
    return localStorage.getItem(STORAGE_KEY);
}

export function applyTheme(theme) {
    const isDark = theme === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light');
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: isDark ? 'dark' : 'light' } }));
}

export function initTheme() {
    const stored = getStoredTheme();

    if (stored === 'dark' || stored === 'light') {
        applyTheme(stored);

        return;
    }

    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(prefersDark ? 'dark' : 'light');
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    applyTheme(isDark ? 'light' : 'dark');
}

// Prevent flash of wrong theme
initTheme();

window.AgroLensTheme = { initTheme, applyTheme, toggleTheme, getStoredTheme };
