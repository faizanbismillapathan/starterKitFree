/**
 * Applies and persists the light/dark/system preference.
 *
 * The class is written to <html> before paint by an inline script in the
 * layout, so this component only handles subsequent changes.
 */
export default (initial = 'system') => ({
    mode: initial,
    open: false,

    init() {
        this.apply();

        // Follow the operating system when the user has not made a choice.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.mode === 'system') {
                this.apply();
            }
        });
    },

    select(mode) {
        this.mode = mode;
        this.open = false;
        this.apply();
        this.persist(mode);
    },

    apply() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const dark = this.mode === 'dark' || (this.mode === 'system' && prefersDark);

        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    },

    /**
     * Persist through a normal form post so the preference survives on the
     * server for authenticated users.
     */
    persist(mode) {
        const form = document.getElementById('theme-form');

        if (!form) {
            return;
        }

        form.querySelector('[name="theme"]').value = mode;
        form.requestSubmit();
    },

    isActive(mode) {
        return this.mode === mode;
    },
});
