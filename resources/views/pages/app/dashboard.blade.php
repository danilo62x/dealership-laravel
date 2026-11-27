{{-- Visão geral — porte de pages/app/DashboardPage.tsx. --}}
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="dashboardPage()">
        <x-app.page-header title="$t('dashboard.title')" subtitle="$t('dashboard.subtitle')" />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-dl.kpi-card title="$t('dashboard.kpi.sales')" value="$t('common.salesCount', { count: kpis.sales })"
                icon="handshake" accent="'brand'" />
            <x-dl.kpi-card title="$t('dashboard.kpi.ticket')" value="$money(kpis.avgTicket)" icon="dollar" accent="'success'" />
            <x-dl.kpi-card title="$t('dashboard.kpi.margin')" value="$money(kpis.totalMargin)"
                hint="kpis.revenue ? $percent(kpis.totalMargin / kpis.revenue) : ''" icon="trending-up" accent="'info'" />
            <x-dl.kpi-card title="$t('dashboard.kpi.turnover')" value="$t('common.daysCount', { count: kpis.avgDays })"
                hint="$t('dashboard.kpi.turnoverHint')" icon="clock" accent="'warning'" />
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[1.5fr_1fr]">
            {{-- ---- Vendas por dia ---- --}}
            <div class="surface-card rounded-2xl border p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 class="font-semibold text-heading" x-text="$t('dashboard.chartTitle')"></h2>
                        <p class="mt-1 text-xs text-muted" x-text="$t('dashboard.chartSubtitle')"></p>
                    </div>
                    <p class="text-sm font-semibold text-heading" x-text="$money(kpis.revenue)"></p>
                </div>
                <div class="mt-4" x-ref="chart"></div>
            </div>

            {{-- ---- Funil ---- --}}
            <div class="surface-card rounded-2xl border p-5 md:p-6">
                <h2 class="font-semibold text-heading" x-text="$t('dashboard.funnelTitle')"></h2>
                <p class="mt-1 text-xs text-muted" x-text="$t('dashboard.funnelSubtitle')"></p>

                <ul class="mt-4 space-y-3">
                    <template x-for="(row, i) in rows" :key="row.stage">
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-heading" x-text="$t('status.deal.' + row.stage)"></span>
                                <span class="text-muted"
                                    x-text="`${$t('common.dealsCount', { count: row.count })} · ${$moneyCompact(row.value)}`"></span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full"
                                    :style="`width: ${Math.round((row.count / maxFunnel) * 100)}%; background-color: ${stageColors[i % stageColors.length]}`"></div>
                            </div>
                        </li>
                    </template>
                </ul>

                <a href="{{ url('/app/propostas') }}"
                    class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand-500 hover:text-brand-600">
                    <span x-text="$t('appNav.deals')"></span>
                    <x-icon name="arrow-right" :size="15" />
                </a>
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-2">
            {{-- ---- Giro lento ---- --}}
            <div class="surface-card rounded-2xl border p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 class="font-semibold text-heading" x-text="$t('dashboard.slowTitle')"></h2>
                        <p class="mt-1 text-xs text-muted" x-text="$t('dashboard.slowSubtitle', { days: s.rules.slowStockDays })"></p>
                    </div>
                    <span class="rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-500"
                        x-text="slow.length"></span>
                </div>

                <template x-if="slow.length">
                    <ul class="mt-4 divide-y divide-[color:var(--tx-border)]">
                        <template x-for="row in slow.slice(0, 6)" :key="row.vehicle.id">
                            <li class="flex items-center gap-3 py-3">
                                <div class="min-w-0 flex-1">
                                    <a :href="'{{ url('/app/veiculo') }}/' + row.vehicle.id"
                                        class="block truncate text-sm font-medium text-heading hover:text-brand-600"
                                        x-text="$q.vehicleLabel(s.models, s.brands, row.vehicle)"></a>
                                    <p class="text-xs text-muted"
                                        x-text="`${$t('common.daysCount', { count: row.days })} · ${$money(row.vehicle.price)}`"></p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-warning-600 dark:text-warning-500"
                                        x-text="'−' + $percent(row.suggestedDiscountPct)"></p>
                                    <p class="text-xs text-muted"
                                        x-text="`${$t('dashboard.suggested')}: ${$money(row.suggestedPrice)}`"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </template>
                <template x-if="!slow.length">
                    <p class="mt-6 text-sm text-muted" x-text="$t('dashboard.slowEmpty')"></p>
                </template>
            </div>

            {{-- ---- Agenda de hoje ---- --}}
            <div class="surface-card rounded-2xl border p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <h2 class="font-semibold text-heading" x-text="$t('dashboard.agendaTitle')"></h2>
                    <a href="{{ url('/app/test-drives') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600"
                        x-text="$t('common.viewAll')"></a>
                </div>

                <template x-if="agenda.length">
                    <ul class="mt-4 divide-y divide-[color:var(--tx-border)]">
                        <template x-for="drive in agenda.slice(0, 6)" :key="drive.id">
                            <li class="flex items-center gap-3 py-3">
                                <span class="w-14 shrink-0 text-sm font-semibold text-heading" x-text="drive.from"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-heading"
                                        x-text="$q.vehicleLabel(s.models, s.brands, $q.vehicleById(s.vehicles, drive.vehicleId))"></p>
                                    <p class="truncate text-xs text-muted"
                                        x-text="`${$q.customerById(s.customers, drive.customerId)?.name ?? '—'} · ${$q.sellerById(s.sellers, drive.sellerId)?.name ?? '—'}`"></p>
                                </div>
                                <x-dl.drive-status-pill status="drive.status" />
                            </li>
                        </template>
                    </ul>
                </template>
                <template x-if="!agenda.length">
                    <x-ui.empty-state icon="calendar" title="$t('dashboard.agendaEmpty')" />
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function dashboardPage() {
                return {
                    chart: null,
                    stageColors: ['#98a2b3', '#0aa295', '#f79009', '#12b76a', '#f04438'],

                    get s() { return $store.dealer },
                    get now() { return $store.clock.now },
                    get kpis() { return $q.stockKpis(this.s.vehicles, this.now, this.s.rules) },
                    get rows() { return $q.funnel(this.s.deals, this.s.vehicles, 90, this.now) },
                    get series() { return $q.salesByDay(this.s.vehicles, 30, this.now) },
                    get slow() { return $q.slowMovers(this.s.vehicles, this.now, this.s.rules) },
                    get agenda() { return $q.drivesOfDay(this.s.drives, window.TODAY_KEY) },
                    get maxFunnel() { return Math.max(1, ...this.rows.map((r) => r.count)) },

                    chartOptions() {
                        const dark = $store.ui.darkMode;
                        const fg = dark ? '#98a2b3' : '#667085';
                        const grid = dark ? '#1f2937' : '#e4e7ec';
                        const series = this.series;
                        return {
                            chart: { type: 'area', height: 280, fontFamily: 'Outfit, sans-serif', foreColor: fg, toolbar: { show: false }, zoom: { enabled: false } },
                            colors: ['#0aa295', '#465fff', '#f79009', '#f04438', '#7a5af8'],
                            grid: { borderColor: grid, strokeDashArray: 4 },
                            tooltip: { theme: dark ? 'dark' : 'light' },
                            legend: { labels: { colors: fg } },
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            fill: { type: 'gradient', gradient: { shadeIntensity: 0.4, opacityFrom: 0.35, opacityTo: 0.02 } },
                            xaxis: { categories: series.map((p) => p.label), tickAmount: 8, tooltip: { enabled: false } },
                            yaxis: { labels: { formatter: (v) => dlFormat.currencyCompact(v) } },
                            series: [{ name: $t('dashboard.chartSeries'), data: series.map((p) => p.value) }],
                        };
                    },
                    init() {
                        this.$nextTick(() => this.render());
                        // Refaz o gráfico quando mudam as vendas, o tema ou o idioma.
                        Alpine.effect(() => {
                            $store.dealer.vehicles.length;
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
