/**
 * Drag & drop upload zone with preview and client-side guard rails.
 *
 * Server-side validation always remains authoritative (12_Form_System.md §13).
 */
export default (options = {}) => ({
    dragging: false,
    filename: '',
    previewUrl: '',
    error: '',
    maxSize: Number(options.maxSize ?? 2048) * 1024,
    accept: options.accept ?? [],

    onDragOver() {
        this.dragging = true;
    },

    onDragLeave() {
        this.dragging = false;
    },

    onDrop(event) {
        this.dragging = false;

        const file = event.dataTransfer?.files?.[0];

        if (file) {
            this.assign(file);
        }
    },

    onSelect(event) {
        const file = event.target.files?.[0];

        if (file) {
            this.assign(file);
        }
    },

    assign(file) {
        this.error = '';

        if (this.accept.length && !this.accept.includes(file.type)) {
            this.error = options.messages?.type ?? 'That file type is not supported.';
            this.reset();
            return;
        }

        if (file.size > this.maxSize) {
            this.error = options.messages?.size ?? 'That file is too large.';
            this.reset();
            return;
        }

        this.filename = file.name;
        this.previewUrl = URL.createObjectURL(file);

        // Keep the native input in sync so the form posts the file.
        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.input.files = transfer.files;
    },

    reset() {
        this.filename = '';

        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = '';
        }

        if (this.$refs.input) {
            this.$refs.input.value = '';
        }
    },

    browse() {
        this.$refs.input?.click();
    },
});
