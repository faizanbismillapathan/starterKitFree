/**
 * Thin ApexCharts wrapper that inherits the design system tokens and reacts to
 * theme changes (18_Dashboard_Module.md §9).
 *
 * ApexCharts is imported dynamically and only once a chart scrolls into view,
 * keeping it out of the main bundle (38_Performance_Guide.md §19, §21).
 */
export default (config = {}) => ({
    chart: null,
    observer: null,
    themeObserver: null,

    init() {
        this.observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                this.observer.disconnect();
                this.render();
            }
        }, { rootMargin: '150px' });

        this.observer.observe(this.$el);

        this.themeObserver = new MutationObserver(() => this.retheme());
        this.themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    },

    destroy() {
        this.observer?.disconnect();
        this.themeObserver?.disconnect();
        this.chart?.destroy();
    },

    async render() {
        if (this.chart) {
            return;
        }

        const { default: ApexCharts } = await import('apexcharts');

        this.chart = new ApexCharts(this.$refs.canvas, this.buildOptions());
        await this.chart.render();

        this.$refs.placeholder?.remove();
    },

    retheme() {
        this.chart?.updateOptions(this.themeOptions(), false, false);
    },

    /**
     * Reads a design token and returns a comma separated rgb() string.
     *
     * The tokens are stored as space separated channels for Tailwind's opacity
     * syntax, but ApexCharts only understands the legacy comma form.
     */
    token(name) {
        const value = getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim();

        if (! value) {
            return undefined;
        }

        return `rgb(${value.split(/\s+/).join(', ')})`;
    },

    isDark() {
        return document.documentElement.classList.contains('dark');
    },

    themeOptions() {
        return {
            chart: { foreColor: this.token('--color-text-muted') },
            grid: { borderColor: this.token('--color-border') },
            tooltip: { theme: this.isDark() ? 'dark' : 'light' },
        };
    },

    buildOptions() {
        const type = config.type ?? 'area';

        const base = {
            chart: {
                type,
                height: config.height ?? 260,
                fontFamily: 'inherit',
                foreColor: this.token('--color-text-muted'),
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { enabled: true, speed: 280 },
                parentHeightOffset: 0,
            },
            colors: (config.colors ?? ['--color-primary']).map((token) =>
                token.startsWith('--') ? this.token(token) : token,
            ),
            series: config.series ?? [],
            dataLabels: { enabled: false },
            grid: {
                borderColor: this.token('--color-border'),
                strokeDashArray: 4,
                padding: { left: 4, right: 4, top: 0 },
            },
            tooltip: {
                theme: this.isDark() ? 'dark' : 'light',
                style: { fontSize: '12px' },
            },
            legend: { show: config.legend ?? false, position: 'bottom', fontSize: '13px' },
        };

        if (type === 'area' || type === 'line') {
            return {
                ...base,
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.26, opacityTo: 0.02, stops: [0, 95] },
                },
                xaxis: {
                    categories: config.categories ?? [],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { fontSize: '12px' }, rotate: 0, hideOverlappingLabels: true },
                    tooltip: { enabled: false },
                },
                yaxis: {
                    labels: { style: { fontSize: '12px' }, formatter: (value) => Math.round(value) },
                    min: 0,
                    forceNiceScale: true,
                },
            };
        }

        if (type === 'donut') {
            return {
                ...base,
                labels: config.labels ?? [],
                // Pie/donut series read their palette from `fill.colors`.
                fill: { colors: base.colors },
                stroke: { width: 0 },
                legend: { show: true, position: 'bottom', fontSize: '13px' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: config.totalLabel ?? '',
                                    fontSize: '13px',
                                    color: this.token('--color-text-muted'),
                                },
                                value: {
                                    fontSize: '22px',
                                    fontWeight: 600,
                                    color: this.token('--color-text'),
                                },
                            },
                        },
                    },
                },
            };
        }

        return {
            ...base,
            plotOptions: {
                bar: { borderRadius: 6, columnWidth: '45%', borderRadiusApplication: 'end' },
            },
            xaxis: {
                categories: config.categories ?? [],
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
        };
    },
});
