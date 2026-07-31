/**
 * Show/hide toggle plus a lightweight strength indicator for password inputs.
 */
export default (options = {}) => ({
    visible: false,
    value: '',
    showStrength: Boolean(options.strength),

    toggle() {
        this.visible = !this.visible;
    },

    get inputType() {
        return this.visible ? 'text' : 'password';
    },

    /**
     * Score from 0-4 based on length and character variety.
     */
    get score() {
        const value = this.value ?? '';

        if (value.length === 0) {
            return 0;
        }

        let score = 0;

        if (value.length >= 10) score++;
        if (value.length >= 14) score++;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
        if (/\d/.test(value) && /[^A-Za-z0-9]/.test(value)) score++;

        return Math.min(score, 4);
    },

    get strengthLabel() {
        return [
            options.labels?.empty ?? '',
            options.labels?.weak ?? 'Weak',
            options.labels?.fair ?? 'Fair',
            options.labels?.good ?? 'Good',
            options.labels?.strong ?? 'Strong',
        ][this.score];
    },

    get strengthClass() {
        return [
            'bg-transparent',
            'bg-[rgb(var(--color-danger))]',
            'bg-[rgb(var(--color-warning))]',
            'bg-[rgb(var(--color-info))]',
            'bg-[rgb(var(--color-success))]',
        ][this.score];
    },
});
