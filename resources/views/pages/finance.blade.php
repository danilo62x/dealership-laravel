{{-- Financiamento — porte de pages/public/FinancePage.tsx. --}}
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{
            freeValue: -1,
            vehicleId: -1,
            freePrice: 90000,
            init() { this.vehicleId = this.showcase[0]?.id ?? this.freeValue },
            get s() { return $store.dealer },
            get rules() { return this.s.rules },
            get showcase() { return $q.availableStock(this.s.vehicles).slice(0, 40) },
            get selected() { return this.showcase.find((v) => v.id === this.vehicleId) },
            get price() { return this.selected ? this.selected.price : this.freePrice },
        }">
        <header class="mb-6">
            <h1 class="text-title-sm font-bold text-heading" x-text="$t('finance.title')"></h1>
            <p class="mt-1 max-w-2xl text-sm text-muted" x-text="$t('finance.subtitle')"></p>
        </header>

        <div class="surface-card mb-5 rounded-2xl border p-5 md:p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="fin-vehicle" x-text="$t('finance.pickVehicle')"></label>
                    <select id="fin-vehicle" x-model.number="vehicleId"
                        class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <option :value="freeValue" x-text="$t('finance.freeValue')"></option>
                        <template x-for="v in showcase" :key="v.id">
                            <option :value="v.id"
                                x-text="`${$q.vehicleLabel(s.models, s.brands, v)} · ${v.modelYear} · ${$money(v.price)}`"></option>
                        </template>
                    </select>
                    <template x-if="selected">
                        <p class="mt-1.5 text-xs text-muted"
                            x-text="$t('finance.ofVehicle', { name: $q.vehicleLabel(s.models, s.brands, selected) })"></p>
                    </template>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="fin-price" x-text="$t('finance.price')"></label>
                    <input id="fin-price" type="number" min="10000" step="1000" :value="price" :disabled="Boolean(selected)"
                        @input="freePrice = Math.max(10000, Number($event.target.value))"
                        class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body disabled:opacity-60" />
                    <p class="mt-1.5 text-xs text-muted"
                        x-text="`${$t('finance.minDown', { value: $money($q.minDown(price, rules)) })} · ${$t('finance.rate')} ${$percent(rules.defaultMonthlyRate)}`"></p>
                </div>
            </div>
        </div>

        <x-dl.finance-simulator price="price" :showMemo="true" :showCompare="true" />

        <section class="surface-card mt-5 rounded-2xl border p-5 md:p-6">
            <h2 class="flex items-center gap-2 font-semibold text-heading">
                <x-icon name="info" :size="18" class="text-brand-500" />
                <span x-text="$t('finance.cet')"></span>
            </h2>
            <p class="mt-2 max-w-3xl text-sm text-muted" x-text="$t('finance.cetHint')"></p>
            <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-themed p-3.5">
                    <dt class="text-xs text-muted" x-text="$t('finance.fee')"></dt>
                    <dd class="mt-1 text-sm font-semibold text-heading" x-text="$money(rules.registrationFee)"></dd>
                </div>
                <div class="rounded-xl border border-themed p-3.5">
                    <dt class="text-xs text-muted" x-text="$t('finance.iof')"></dt>
                    <dd class="mt-1 text-sm font-semibold text-heading" x-text="$percent(rules.iocPct)"></dd>
                </div>
                <div class="rounded-xl border border-themed p-3.5">
                    <dt class="text-xs text-muted" x-text="$t('settings.rules.minDownPct')"></dt>
                    <dd class="mt-1 text-sm font-semibold text-heading" x-text="$percent(rules.minDownPct)"></dd>
                </div>
            </dl>
        </section>
    </div>
</x-layout.public>
