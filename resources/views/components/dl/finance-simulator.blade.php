@props([
    'price' => '0',
    'tradeIn' => '0',
    'showMemo' => true,
    'showCompare' => false,
])
{{-- Simulador ao vivo — porte de components/dl/FinanceSimulator.tsx.

     Mexer na entrada, no prazo ou na taxa recalcula na hora pela Tabela Price,
     e a entrada nunca desce da mínima do estoque — a tela diz por quê.
     $price e $tradeIn são expressões Alpine. --}}
<div class="space-y-5"
    x-data="{
        down: 0,
        months: 48,
        rate: $store.dealer.rules.defaultMonthlyRate,
        init() {
            this.down = this.floor;
            // O preço muda quando o visitante troca de veículo: a entrada
            // volta ao piso, exatamente como o useEffect do React.
            this.$watch('price', () => { this.down = this.floor });
        },
        get rules() { return $store.dealer.rules },
        get price() { return Math.max(0, Number({{ $price }}) || 0) },
        get tradeIn() { return Math.max(0, Number({{ $tradeIn }}) || 0) },
        get floor() { return $q.minDown(this.price, this.rules) },
        get effectiveDown() { return Math.max(this.floor, Math.min(this.down, this.price)) },
        get belowFloor() { return this.down < this.floor },
        get simulation() {
            return $q.simulate(
                { price: this.price, down: this.effectiveDown, months: this.months, rate: this.rate, tradeIn: this.tradeIn },
                this.rules
            );
        },
        get comparison() {
            return [24, 36, 48, 60].map((n) => ({
                months: n,
                sim: $q.simulate(
                    { price: this.price, down: this.effectiveDown, months: n, rate: this.rate, tradeIn: this.tradeIn },
                    this.rules
                ),
            }));
        },
        get cheapest() {
            return this.comparison.reduce((best, row) => (row.sim.total < best.sim.total ? row : best), this.comparison[0]);
        },
    }">
    <div class="surface-card rounded-2xl border p-5 md:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_1fr]">
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 flex items-center justify-between text-sm font-medium text-heading" for="sim-down">
                        <span x-text="$t('finance.down')"></span>
                        <span class="text-xs font-normal text-muted" x-text="$t('finance.minDown', { value: $money(floor) })"></span>
                    </label>
                    <input id="sim-down" type="number" :min="floor" :max="price" step="500" x-model.number="down"
                        class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm font-medium text-body" />
                    <input type="range" :min="floor" :max="price" step="500" :value="effectiveDown"
                        @input="down = Number($event.target.value)" class="mt-2 w-full" :aria-label="$t('finance.down')" />

                    <template x-if="belowFloor">
                        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-warning-600 dark:text-warning-500">
                            <x-icon name="alert-triangle" :size="14" class="mt-0.5" />
                            <span x-text="$t('finance.minDownHint', { pct: $percent(1 - rules.minDownPct) })"></span>
                        </p>
                    </template>
                    <template x-if="tradeIn > 0">
                        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-success-600 dark:text-success-500">
                            <x-icon name="check-circle" :size="14" class="mt-0.5" />
                            <span x-text="$t('finance.tradeInApplied', { value: $money(tradeIn) })"></span>
                        </p>
                    </template>
                </div>

                <div>
                    <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('finance.months')"></span>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="n in [24, 36, 48, 60]" :key="n">
                            <button type="button" @click="months = n"
                                class="focus-ring rounded-lg border px-4 py-2 text-sm font-medium transition"
                                :class="months === n ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body hover:text-heading'"
                                x-text="$t('finance.monthsUnit', { months: n })"></button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="sim-rate" x-text="$t('finance.rate')"></label>
                    <div class="flex items-center gap-3">
                        <input id="sim-rate" type="range" min="0.005" max="0.035" step="0.0005" x-model.number="rate" class="w-full" />
                        <span class="w-20 shrink-0 text-right text-sm font-semibold text-heading" x-text="$rate(rate)"></span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-brand-50 p-5 dark:bg-brand-500/10">
                <p class="text-xs tracking-wider text-muted uppercase" x-text="$t('finance.installment')"></p>
                <p class="mt-1 text-title-sm font-bold text-brand-600 dark:text-brand-400">
                    <span x-text="$money(simulation.installment)"></span>
                    <span class="text-base font-medium text-muted" x-text="$t('common.perMonth')"></span>
                </p>

                <dl class="mt-5 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                        <dt class="text-muted" x-text="$t('finance.financed')"></dt>
                        <dd class="font-medium text-heading" x-text="$money(simulation.financed)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                        <dt class="text-muted" x-text="$t('finance.total')"></dt>
                        <dd class="font-medium text-heading" x-text="$money(simulation.total)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                        <dt class="text-muted" x-text="$t('finance.interest')"></dt>
                        <dd class="font-medium text-heading" x-text="$money(simulation.interest)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                        <dt class="text-muted" x-text="$t('finance.cet')"></dt>
                        <dd class="font-bold text-heading" x-text="$rate(simulation.cet)"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    @if ($showCompare)
        <div class="surface-card rounded-2xl border p-5 md:p-6">
            <h3 class="font-semibold text-heading" x-text="$t('finance.compare.title')"></h3>
            <p class="mt-1 text-xs text-muted" x-text="$t('finance.compare.subtitle')"></p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <template x-for="row in comparison" :key="row.months">
                    <button type="button" @click="months = row.months" class="rounded-xl border p-4 text-left transition"
                        :class="months === row.months ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-themed hover:border-brand-300'">
                        <p class="text-xs font-medium text-muted" x-text="$t('finance.monthsUnit', { months: row.months })"></p>
                        <p class="mt-1 text-lg font-bold text-heading" x-text="$money(row.sim.installment)"></p>
                        <p class="mt-1 text-xs text-muted" x-text="`${$t('finance.total')}: ${$money(row.sim.total)}`"></p>
                        <p class="text-xs text-muted" x-text="`${$t('finance.cet')}: ${$rate(row.sim.cet)}`"></p>
                        <template x-if="row.months === cheapest.months">
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                <x-icon name="check" :size="11" :strokeWidth="2.5" />
                                <span x-text="$t('finance.compare.cheapest')"></span>
                            </span>
                        </template>
                    </button>
                </template>
            </div>
        </div>
    @endif

    @if ($showMemo)
        <x-dl.finance-memo simulation="simulation" />
    @endif
</div>
