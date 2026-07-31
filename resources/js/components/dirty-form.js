/**
 * Warns before navigating away from a form with unsaved changes
 * (12_Form_System.md §25).
 */
export default (message = 'You have unsaved changes.') => ({
    dirty: false,
    submitting: false,

    init() {
        this.$el.addEventListener('input', () => {
            this.dirty = true;
        });

        this.$el.addEventListener('submit', () => {
            this.submitting = true;
        });

        window.addEventListener('beforeunload', (event) => {
            if (!this.dirty || this.submitting) {
                return;
            }

            event.preventDefault();
            event.returnValue = message;
        });
    },
});
