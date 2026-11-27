{{-- Estoque do showroom — porte de pages/app/StockAdminPage.tsx.

     Margem e dias parados não são campos: saem de `margin()` e `daysInStock()`
     a cada linha, e o giro lento é a mesma regra da barra lateral. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('stockAdmin.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="stockAdminPage()">
        <x-app.page-header title="$t('stockAdmin.title')" subtitle="$t('stockAdmin.subtitle')" :crumbs="$crumbs">
            <x-slot:action>
                <button type="button" @click="open = true"
                    class="focus-ring flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <x-icon name="plus" :size="16" />
                    <span x-text="$t('stockAdmin.new')"></span>
                </button>
            </x-slot:action>
        </x-app.page-header>

        <x-ui.filter-bar search="term" searchPlaceholder="$t('stockAdmin.searchPlaceholder')">
            <x-ui.filter-select model="state" label="$t('common.status')">
                <option value="" x-text="$t('common.all')"></option>
                <template x-for="st in states" :key="st">
                    <option :value="st" x-text="$t('status.stock.' + st)"></option>
                </template>
            </x-ui.filter-select>
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" x-model="onlySlow" class="h-4 w-4 rounded border-themed accent-brand-500" />
                <span x-text="$t('stockAdmin.onlySlow')"></span>
            </label>
        </x-ui.filter-bar>

        <template x-if="rows.length">
            <div>
                <div class="surface-card overflow-hidden rounded-2xl border">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-themed text-xs text-muted">
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.vehicle')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.year')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.km')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.cost')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.price')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.margin')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.days')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('stockAdmin.columns.state')"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="v in pageItems" :key="v.id">
                                    <tr class="border-b border-themed last:border-0 hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-5 py-3">
                                            <a :href="'{{ url('/app/veiculo') }}/' + v.id"
                                                class="font-medium text-heading hover:text-brand-600"
                                                x-text="$q.vehicleLabel(s.models, s.brands, v)"></a>
                                        </td>
                                        <td class="px-5 py-3 text-body" x-text="$q.yearLabel(v)"></td>
                                        <td class="px-5 py-3 text-body" x-text="`${$number(v.km)} km`"></td>
                                        <td class="px-5 py-3 text-body" x-text="$money(v.acquisitionCost + v.prepCost)"></td>
                                        <td class="px-5 py-3 font-medium text-heading" x-text="$money(v.price)"></td>
                                        <td class="px-5 py-3">
                                            <span :class="$q.margin(v, v.price) < 0
                                                    ? 'font-medium text-error-600 dark:text-error-500'
                                                    : 'font-medium text-success-600 dark:text-success-500'">
                                                <span x-text="$money($q.margin(v, v.price))"></span>
                                                <span class="ml-1 text-xs font-normal text-muted" x-text="$percent($q.marginPct(v, v.price))"></span>
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span :class="slowIds.includes(v.id) ? 'font-semibold text-warning-600 dark:text-warning-500' : 'text-body'"
                                                x-text="$t('common.daysCount', { count: $q.daysInStock(v, now) })"></span>
                                        </td>
                                        <td class="px-5 py-3"><x-dl.stock-pill state="v.state" /></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <x-ui.pagination page="page" totalPages="totalPages" change="page = $event" />
                </div>
            </div>
        </template>

        <template x-if="!rows.length">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="car" title="$t('stockAdmin.empty')" description="$t('stockAdmin.emptyDesc')" />
            </div>
        </template>

        {{-- ---- Entrada de veículo ---- --}}
        <x-ui.modal open="open" close="open = false" title="$t('stockAdmin.form.title')" size="xl">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-model" x-text="$t('stockAdmin.form.model')"></label>
                    <select id="v-model" x-model.number="form.modelId" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <template x-for="m in s.models" :key="m.id">
                            <option :value="m.id" x-text="`${$q.brandById(s.brands, m.brandId)?.name} ${m.name}`"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-version" x-text="$t('stockAdmin.form.version')"></label>
                    <input id="v-version" x-model="form.version" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-color" x-text="$t('stockAdmin.form.color')"></label>
                    <input id="v-color" x-model="form.color" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-year" x-text="$t('stockAdmin.form.year')"></label>
                    <input id="v-year" type="number" x-model.number="form.year" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-myear" x-text="$t('stockAdmin.form.modelYear')"></label>
                    <input id="v-myear" type="number" x-model.number="form.modelYear" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-km" x-text="$t('stockAdmin.form.km')"></label>
                    <input id="v-km" type="number" x-model.number="form.km" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-tr" x-text="$t('stockAdmin.form.transmission')"></label>
                    <select id="v-tr" x-model="form.transmission" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <template x-for="tr in transmissions" :key="tr">
                            <option :value="tr" x-text="$t('spec.transmission.' + tr)"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-fuel" x-text="$t('stockAdmin.form.fuel')"></label>
                    <select id="v-fuel" x-model="form.fuel" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <template x-for="f in fuels" :key="f">
                            <option :value="f" x-text="$t('spec.fuel.' + f)"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-doors" x-text="$t('stockAdmin.form.doors')"></label>
                    <input id="v-doors" type="number" min="2" max="5" x-model.number="form.doors" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-plate" x-text="$t('stockAdmin.form.plateEnd')"></label>
                    <input id="v-plate" x-model="form.plateEnd" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-acq" x-text="$t('stockAdmin.form.acquisitionCost')"></label>
                    <input id="v-acq" type="number" x-model.number="form.acquisitionCost" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-prep" x-text="$t('stockAdmin.form.prepCost')"></label>
                    <input id="v-prep" type="number" x-model.number="form.prepCost" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-price" x-text="$t('stockAdmin.form.price')"></label>
                    <input id="v-price" type="number" x-model.number="form.price" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="v-state" x-text="$t('stockAdmin.form.state')"></label>
                    <select id="v-state" x-model="form.state" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <template x-for="st in states" :key="st">
                            <option :value="st" x-text="$t('status.stock.' + st)"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('vehicle.options')"></span>
                <div class="flex flex-wrap gap-2">
                    <template x-for="opt in window.OPTION_CATALOG" :key="opt">
                        <button type="button" @click="toggleOption(opt)"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition"
                            :class="form.options.includes(opt)
                                ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'
                                : 'border-themed text-muted hover:text-body'">
                            <template x-if="form.options.includes(opt)">
                                <span class="contents"><x-icon name="check" :size="12" :strokeWidth="2.5" /></span>
                            </template>
                            <span x-text="opt"></span>
                        </button>
                    </template>
                </div>
            </div>

            <p class="mt-4 rounded-xl border p-3.5 text-sm"
                :class="formMargin < 0
                    ? 'border-error-200 bg-error-50 text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-500'
                    : 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-500'"
                x-text="$t('stockAdmin.form.marginPreview', { value: $money(formMargin), pct: $percent(formMarginPct) })"></p>

            <x-slot:footer>
                <button type="button" @click="open = false"
                    class="focus-ring rounded-lg border border-themed px-4 py-2 text-sm font-medium text-body" x-text="$t('common.cancel')"></button>
                <button type="button" @click="submit()"
                    class="focus-ring rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                    x-text="$t('common.save')"></button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    @push('scripts')
        <script>
            function stockAdminPage() {
                const params = new URLSearchParams(window.location.search);
                const year = new Date().getFullYear();
                return {
                    term: '',
                    state: '',
                    onlySlow: params.get('giro') === 'lento',
                    open: params.get('novo') === '1',
                    page: 1,
                    pageSize: 12,
                    states: ['disponivel', 'reservado', 'vendido', 'preparacao'],
                    transmissions: ['manual', 'automatico', 'cvt', 'automatizado'],
                    fuels: ['flex', 'gasolina', 'diesel', 'hibrido', 'eletrico'],
                    form: {
                        modelId: 1,
                        version: '1.0 Turbo',
                        year: year - 2,
                        modelYear: year - 1,
                        km: 24000,
                        color: 'Branco',
                        plateEnd: '7',
                        transmission: 'automatico',
                        fuel: 'flex',
                        doors: 4,
                        acquisitionCost: 62000,
                        prepCost: 2400,
                        price: 74900,
                        state: 'preparacao',
                        options: ['Ar-condicionado', 'Central multimídia'],
                    },

                    init() {
                        this.form.modelId = $store.dealer.models[0]?.id ?? 1;
                        this.$watch('term', () => { this.page = 1 });
                        this.$watch('state', () => { this.page = 1 });
                        this.$watch('onlySlow', () => { this.page = 1 });
                    },

                    get s() { return $store.dealer },
                    get now() { return $store.clock.now },
                    get slowIds() { return $q.slowMovers(this.s.vehicles, this.now, this.s.rules).map((row) => row.vehicle.id) },
                    get rows() {
                        let list = $q.filterStock(this.s.vehicles, this.s.models, this.s.brands, { term: this.term });
                        if (this.state) list = list.filter((v) => v.state === this.state);
                        if (this.onlySlow) {
                            const ids = this.slowIds;
                            list = list.filter((v) => ids.includes(v.id));
                        }
                        return [...list].sort((a, b) => $q.daysInStock(b, this.now) - $q.daysInStock(a, this.now));
                    },
                    get totalPages() { return Math.max(1, Math.ceil(this.rows.length / this.pageSize)) },
                    get pageItems() { return this.rows.slice((this.page - 1) * this.pageSize, this.page * this.pageSize) },

                    get formMargin() { return this.form.price - this.form.acquisitionCost - this.form.prepCost },
                    get formMarginPct() { return this.form.price > 0 ? this.formMargin / this.form.price : 0 },

                    toggleOption(opt) {
                        this.form.options = this.form.options.includes(opt)
                            ? this.form.options.filter((o) => o !== opt)
                            : [...this.form.options, opt];
                    },
                    submit() {
                        $store.dealer.saveVehicle({ ...this.form });
                        this.open = false;
                        $store.ui.notifySuccess($t('stockAdmin.saved'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
