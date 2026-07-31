/**
 * Auto-dismissing toast notifications (15_Notification_System.md §7).
 *
 * Critical messages require manual dismissal.
 */
export default () => ({
    toasts: [],
    nextId: 1,

    init() {
        window.addEventListener('toast', (event) => this.push(event.detail ?? {}));
    },

    push({ type = 'info', message = '', title = '', duration = 5000 }) {
        if (!message) {
            return;
        }

        const id = this.nextId++;

        this.toasts.push({ id, type, message, title });

        if (type !== 'critical' && duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }
    },

    dismiss(id) {
        this.toasts = this.toasts.filter((toast) => toast.id !== id);
    },

    iconFor(type) {
        return {
            success: 'check-circle',
            danger: 'x-circle',
            critical: 'x-circle',
            warning: 'exclamation-triangle',
            info: 'information-circle',
        }[type] ?? 'information-circle';
    },
});
