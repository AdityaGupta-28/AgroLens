<script>
    (function () {
        const key = 'agrolens-theme';
        const stored = localStorage.getItem(key);
        const dark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    })();
</script>
