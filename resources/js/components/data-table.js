/**
 * Client-side behaviour for the data table: debounced search, bulk selection
 * and column visibility (13_Table_System.md §9, §12, §22).
 *
 * Filtering, sorting and pagination remain server-side.
 */
export default (options = {}) => ({
    search: options.search ?? '',
    selected: [],
    columnsOpen: false,
    hidden: [],
    storageKey: options.storageKey ?? null,
    timer: null,

    init() {
        this.restoreColumns();
    },

    /**
     * Debounced submit so each keystroke does not hit the server (§9).
     */
    onSearch() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.$refs.filters?.requestSubmit(), 350);
    },

    get allVisibleSelected() {
        const ids = this.visibleIds();

        return ids.length > 0 && ids.every((id) => this.selected.includes(id));
    },

    toggleAll() {
        this.selected = this.allVisibleSelected ? [] : this.visibleIds();
    },

    clearSelection() {
        this.selected = [];
    },

    visibleIds() {
        return Array.from(this.$root.querySelectorAll('[data-row-id]'))
            .map((row) => row.dataset.rowId);
    },

    isHidden(column) {
        return this.hidden.includes(column);
    },

    toggleColumn(column) {
        this.hidden = this.isHidden(column)
            ? this.hidden.filter((item) => item !== column)
            : [...this.hidden, column];

        this.persistColumns();
    },

    /** Column preferences are remembered per table (§22). */
    persistColumns() {
        if (!this.storageKey) {
            return;
        }

        window.localStorage.setItem(this.storageKey, JSON.stringify(this.hidden));
    },

    restoreColumns() {
        if (!this.storageKey) {
            return;
        }

        try {
            const stored = window.localStorage.getItem(this.storageKey);
            this.hidden = stored ? JSON.parse(stored) : [];
        } catch {
            this.hidden = [];
        }
    },
});
