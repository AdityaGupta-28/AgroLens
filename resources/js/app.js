import './bootstrap';
import './theme';

function loadChartsWhenNeeded() {
    if (
        document.getElementById('holdingChart') ||
        document.getElementById('irrigationChart') ||
        document.getElementById('cropChart') ||
        document.getElementById('wellChart')
    ) {
        import('./charts');
    }
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('themeToggle', () => ({
        dark: document.documentElement.classList.contains('dark'),

        init() {
            window.addEventListener('theme-changed', (e) => {
                this.dark = e.detail.theme === 'dark';
            });
        },

        toggle() {
            window.AgroLensTheme.toggleTheme();
            this.dark = document.documentElement.classList.contains('dark');
        },
    }));
});

document.addEventListener('DOMContentLoaded', loadChartsWhenNeeded);
document.addEventListener('livewire:navigated', loadChartsWhenNeeded);
