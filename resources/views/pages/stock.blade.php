{{-- Estoque público — porte de pages/public/StockPage.tsx. --}}
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{
            pageSize: 12,
            page: 1,
            term: '',
            brandId: '',
            category: '',
            transmission: '',
            fuel: '',
            color: '',
            yearMin: '',
            priceMax: '',
            kmMax: '',
            sort: 'recent',
            categories: ['hatch', 'sedan', 'suv', 'picape', 'utilitario'],
            transmissions: ['manual', 'automatico', 'cvt', 'automatizado'],
            fuels: ['flex', 'gasolina', 'diesel', 'hibrido', 'eletrico'],
            priceSteps: [50000, 80000, 110000, 150000, 220000],
            kmSteps: [20000, 50000, 80000, 120000],
            sorts: [
                { value: 'recent', key: 'stock.sort.recent' },
                { value: 'price-asc', key: 'stock.sort.priceAsc' },
                { value: 'price-desc', key: 'stock.sort.priceDesc' },
                { value: 'km-asc', key: 'stock.sort.kmAsc' },
                { value: 'year-desc', key: 'stock.sort.yearDesc' },
            ],

            init() {
                // Os filtros da home chegam pela URL (?marca, ?segmento, ?preco).
                const params = new URLSearchParams(window.location.search);
                this.brandId = params.get('marca') ?? '';
                this.category = params.get('segmento') ?? '';
                this.priceMax = params.get('preco') ?? '';
                this.$watch('term', () => { this.page = 1 });
                this.$watch('brandId', () => { this.page = 1 });
                this.$watch('category', () => { this.page = 1 });
                this.$watch('transmission', () => { this.page = 1 });
                this.$watch('fuel', () => { this.page = 1 });
                this.$watch('color', () => { this.page = 1 });
                this.$watch('yearMin', () => { this.page = 1 });
                this.$watch('priceMax', () => { this.page = 1 });
                this.$watch('kmMax', () => { this.page = 1 });
                this.$watch('sort', () => { this.page = 1 });
            },

            get s() { return $store.dealer },
            get showcase() { return $q.availableStock(this.s.vehicles) },
            get colors() { return [...new Set(this.showcase.map((v) => v.color))].sort() },
            get years() { return [...new Set(this.showcase.map((v) => v.modelYear))].sort((a, b) => b - a) },
            get brandModels() {
                return this.brandId ? this.s.models.filter((m) => m.brandId === Number(this.brandId)) : this.s.models;
            },
            get filtered() {
                return $q.sortStock(
                    $q.filterStock(this.showcase, this.s.models, this.s.brands, {
                        term: this.term,
                        brandId: this.brandId ? Number(this.brandId) : null,
                        category: this.category,
                        transmission: this.transmission,
                        fuel: this.fuel,
                        color: this.color,
                        yearMin: this.yearMin ? Number(this.yearMin) : null,
                        priceMax: this.priceMax ? Number(this.priceMax) : null,
                        kmMax: this.kmMax ? Number(this.kmMax) : null,
                    }),
                    this.sort
                );
            },
            get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.pageSize)) },
            get pageItems() { return this.filtered.slice((this.page - 1) * this.pageSize, this.page * this.pageSize) },
            clear() {
                this.term = ''; this.brandId = ''; this.category = ''; this.transmission = '';
                this.fuel = ''; this.color = ''; this.yearMin = ''; this.priceMax = ''; this.kmMax = '';
                window.history.replaceState({}, '', '{{ url('/estoque') }}');
            },
        }">
        <header class="mb-6">
            <h1 class="text-title-sm font-bold text-heading" x-text="$t('stock.title')"></h1>
            <p class="mt-1 text-sm text-muted" x-text="$t('stock.subtitle')"></p>
        </header>

        <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
            {{-- ---- Filtros ---- --}}
            <aside class="surface-card h-fit rounded-2xl border p-5">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-semibold text-heading">
                        <x-icon name="filter" :size="16" />
                        <span x-text="$t('common.filters')"></span>
                    </h2>
                    <button type="button" @click="clear()" class="text-xs font-medium text-brand-500 hover:text-brand-600"
                        x-text="$t('common.clearFilters')"></button>
                </div>

                <div class="mt-4 space-y-3.5">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-term" x-text="$t('common.search')"></label>
                        <input id="f-term" x-model="term" :placeholder="$t('stock.searchPlaceholder')"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-brand" x-text="$t('stock.brand')"></label>
                        <select id="f-brand" x-model="brandId"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <option value="" x-text="$t('common.all')"></option>
                            <template x-for="b in s.brands" :key="b.id">
                                <option :value="b.id" x-text="b.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-model" x-text="$t('stock.model')"></label>
                        <select id="f-model" x-model="category" :aria-label="$t('stock.category')"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <option value="" x-text="$t('common.all')"></option>
                            <template x-for="c in categories" :key="c">
                                <option :value="c" x-text="$t('spec.category.' + c)"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-[11px] text-muted" x-text="$t('common.vehiclesCount', { count: brandModels.length })"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-muted" for="f-year" x-text="$t('stock.yearMin')"></label>
                            <select id="f-year" x-model="yearMin"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                                <option value="" x-text="$t('common.all')"></option>
                                <template x-for="y in years" :key="y">
                                    <option :value="y" x-text="y"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-muted" for="f-km" x-text="$t('stock.kmMax')"></label>
                            <select id="f-km" x-model="kmMax"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                                <option value="" x-text="$t('common.all')"></option>
                                <template x-for="k in kmSteps" :key="k">
                                    <option :value="k" x-text="$number(k)"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-price" x-text="$t('stock.priceMax')"></label>
                        <select id="f-price" x-model="priceMax"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <option value="" x-text="$t('common.all')"></option>
                            <template x-for="p in priceSteps" :key="p">
                                <option :value="p" x-text="$money(p)"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-tr" x-text="$t('stock.transmission')"></label>
                        <select id="f-tr" x-model="transmission"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <option value="" x-text="$t('common.all')"></option>
                            <template x-for="tr in transmissions" :key="tr">
                                <option :value="tr" x-text="$t('spec.transmission.' + tr)"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <span class="mb-1 block text-xs font-medium text-muted" x-text="$t('stock.fuel')"></span>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="f in fuels" :key="f">
                                <button type="button" @click="fuel = (fuel === f ? '' : f)" class="rounded-full transition"
                                    :class="fuel === f ? 'ring-2 ring-brand-500' : 'opacity-70 hover:opacity-100'">
                                    <x-dl.fuel-badge fuel="f" />
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted" for="f-color" x-text="$t('stock.color')"></label>
                        <select id="f-color" x-model="color"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <option value="" x-text="$t('common.all')"></option>
                            <template x-for="c in colors" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </aside>

            {{-- ---- Resultados ---- --}}
            <div class="min-w-0">
                <div class="surface-card mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3.5">
                    <p class="text-sm text-muted" x-text="$t('common.resultsCount', { count: filtered.length })"></p>
                    <div class="flex items-center gap-2">
                        <select id="stock-sort" x-model="sort" :aria-label="$t('common.sortBy')"
                            class="focus-ring rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
                            <template x-for="opt in sorts" :key="opt.value">
                                <option :value="opt.value" x-text="$t(opt.key)"></option>
                            </template>
                        </select>
                        <div class="flex overflow-hidden rounded-lg border border-themed">
                            <button type="button" @click="$store.ui.setStockView('grid')" :aria-label="$t('common.grid')"
                                class="flex h-9 w-9 items-center justify-center"
                                :class="$store.ui.stockView === 'grid' ? 'bg-brand-500 text-white' : 'text-muted'">
                                <x-icon name="grid" :size="16" />
                            </button>
                            <button type="button" @click="$store.ui.setStockView('list')" :aria-label="$t('common.list')"
                                class="flex h-9 w-9 items-center justify-center"
                                :class="$store.ui.stockView === 'list' ? 'bg-brand-500 text-white' : 'text-muted'">
                                <x-icon name="list" :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <template x-if="pageItems.length">
                    <div>
                        <div :class="$store.ui.stockView === 'grid' ? 'grid gap-5 sm:grid-cols-2 xl:grid-cols-3' : 'grid gap-4 sm:grid-cols-2'">
                            <template x-for="v in pageItems" :key="v.id">
                                <div><x-dl.vehicle-card vehicle="v" compact="$store.ui.stockView === 'list'" /></div>
                            </template>
                        </div>
                        <div class="mt-8">
                            <x-ui.pagination page="page" totalPages="totalPages" change="page = $event" />
                        </div>
                    </div>
                </template>

                <template x-if="!pageItems.length">
                    <div class="surface-card rounded-2xl border">
                        <x-ui.empty-state icon="car" title="$t('stock.empty')" description="$t('stock.emptyDesc')">
                            <button type="button" @click="clear()"
                                class="focus-ring rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                                x-text="$t('common.clearFilters')"></button>
                        </x-ui.empty-state>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-layout.public>
