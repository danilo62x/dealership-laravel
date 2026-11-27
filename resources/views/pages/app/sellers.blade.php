{{-- Vendedores — porte de pages/app/SellersPage.tsx.
     Metas, vendas e comissão derivada por faixa de margem. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('sellers.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="sellersPage()">
        <x-app.page-header title="$t('sellers.title')" subtitle="$t('sellers.subtitle')" :crumbs="$crumbs" />

        <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
            <div class="surface-card overflow-hidden rounded-2xl border">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-themed text-xs text-muted">
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.seller')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.sales')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.goal')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.revenue')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.margin')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('sellers.columns.commission')"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in rows" :key="row.seller.id">
                                <tr class="border-b border-themed last:border-0">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <x-ui.avatar src="row.seller.avatar" name="row.seller.name" size="sm" />
                                            <div class="min-w-0">
                                                <span class="block truncate font-medium text-heading" x-text="row.seller.name"></span>
                                                <span class="block truncate text-xs text-muted"
                                                    x-text="$t('sellers.since', { date: $date(row.seller.since) })"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-semibold text-heading" x-text="row.sales"></td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs text-muted"
                                            x-text="$t('sellers.goalProgress', { done: row.sales, goal: row.goal })"></span>
                                        <span class="mt-1 block h-2 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span class="block h-full rounded-full"
                                                :class="row.goalPct >= 1 ? 'bg-success-500' : 'bg-brand-500'"
                                                :style="`width: ${Math.min(100, Math.round(row.goalPct * 100))}%`"></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-body" x-text="$money(row.revenue)"></td>
                                    <td class="px-5 py-3">
                                        <span :class="row.margin < 0
                                                ? 'font-medium text-error-600 dark:text-error-500'
                                                : 'font-medium text-success-600 dark:text-success-500'"
                                            x-text="$money(row.margin)"></span>
                                    </td>
                                    <td class="px-5 py-3 font-semibold text-brand-600 dark:text-brand-400">
                                        <span x-text="$money(row.commission)"></span>
                                        <template x-if="row.margin > 0">
                                            <span class="ml-1 text-xs font-normal text-muted" x-text="$percent(row.commission / row.margin)"></span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-5">
                <div class="surface-card rounded-2xl border p-5 md:p-6">
                    <h2 class="font-semibold text-heading" x-text="$t('sellers.columns.commission')"></h2>
                    <div class="mt-3" x-ref="chart"></div>
                </div>

                <x-dl.commission-table />
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function sellersPage() {
                return {
                    chart: null,

                    get s() { return $store.dealer },
                    get rows() {
                        return $q.sellerPerformance(this.s.sellers, this.s.deals, this.s.vehicles, $store.clock.now, this.s.rules);
                    },

                    chartOptions() {
                        const dark = $store.ui.darkMode;
                        const fg = dark ? '#98a2b3' : '#667085';
                        const grid = dark ? '#1f2937' : '#e4e7ec';
                        const rows = this.rows;
                        return {
                            chart: { type: 'bar', height: 260, fontFamily: 'Outfit, sans-serif', foreColor: fg, toolbar: { show: false }, zoom: { enabled: false } },
                            colors: ['#0aa295', '#465fff', '#f79009', '#f04438', '#7a5af8'],
                            grid: { borderColor: grid, strokeDashArray: 4 },
                            tooltip: { theme: dark ? 'dark' : 'light' },
                            legend: { labels: { colors: fg } },
                            dataLabels: { enabled: false },
                            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                            xaxis: { categories: rows.map((r) => r.seller.name.split(' ')[0]) },
                            yaxis: { labels: { formatter: (v) => dlFormat.currencyCompact(v) } },
                            series: [{ name: $t('sellers.columns.commission'), data: rows.map((r) => r.commission) }],
                        };
                    },
                    init() {
                        this.$nextTick(() => this.render());
                        Alpine.effect(() => {
                            $store.dealer.deals.length;
                            $store.ui.darkMode;
                            $store.ui.lang;
                            this.$nextTick(() => this.render());
                        });
                    },
                    render() {
                        const el = this.$refs.chart;
                        if (!el) return;
                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }
                        this.chart = new ApexCharts(el, this.chartOptions());
                        this.chart.render();
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
