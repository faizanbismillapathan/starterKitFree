/**
 * Drives the confirmation dialog required before destructive actions
 * (14_Modal_System.md §10).
 */
export default () => ({
    open: false,
    submitting: false,
    previouslyFocused: null,

    show() {
        this.previouslyFocused = document.activeElement;
        this.open = true;

        this.$nextTick(() => {
            const target = this.$refs.panel?.querySelector('[data-autofocus]')
                ?? this.$refs.panel?.querySelector('button, [href], input, select, textarea');

            target?.focus();
        });
    },

    close() {
        if (this.submitting) {
            return;
        }

        this.open = false;
        // Focus returns to the trigger (14_Modal_System.md §18).
        this.$nextTick(() => this.previouslyFocused?.focus());
    },

    confirm() {
        this.submitting = true;
        this.$refs.form?.submit();
    },
});
