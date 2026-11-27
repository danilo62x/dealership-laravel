{{-- Início — porte de pages/public/HomePage.tsx. --}}
@php $shop = \App\Support\DlData::store(); @endphp
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div x-data="{
            brandId: '',
            category: '',
            priceMax: '',
            priceSteps: [60000, 90000, 130000, 200000],
            categories: [
                { key: 'hatch', icon: 'car' },
                { key: 'sedan', icon: 'car' },
                { key: 'suv', icon: 'truck' },
                { key: 'picape', icon: 'truck' },
                { key: 'utilitario', icon: 'package' },
            ],
            why: [
                { key: 'a', icon: 'calculator' },
                { key: 'b', icon: 'refresh-cw' },
                { key: 'c', icon: 'calendar' },
            ],
            get s() { return $store.dealer },
            get showcase() { return $q.availableStock(this.s.vehicles) },
            get highlights() { return $q.weeklyHighlights(this.s.vehicles, 4) },
            get arrivals() { return $q.freshArrivals(this.s.vehicles, 8).slice(0, 4) },
            get cheapest() { return this.showcase.reduce((min, v) => (v.price < min ? v.price : min), this.showcase[0]?.price ?? 0) },
            search() {
                const params = new URLSearchParams();
                if (this.brandId) params.set('marca', this.brandId);
                if (this.category) params.set('segmento', this.category);
                if (this.priceMax) params.set('preco', this.priceMax);
                window.location.href = '{{ url('/estoque') }}?' + params.toString();
            },
        }">
        {{-- ---- Vitrine em destaque ---- --}}
        <section class="relative isolate overflow-hidden border-b border-themed">
            <img src="{{ \App\Support\DlData::image($shop['coverSeed'], 1600, 900) }}" alt=""
                class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="grid items-start gap-10 lg:grid-cols-[1.15fr_1fr]">
                    <div class="min-w-0">
                        <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">
                            <x-icon name="shield-check" :size="13" />
                            <span x-text="$t('home.hero.eyebrow')"></span>
                        </p>
                        <h1 class="mt-5 text-3xl font-bold text-white sm:text-title-md lg:text-title-lg" x-text="$t('home.hero.title')"></h1>
                        <p class="mt-4 max-w-2xl text-base text-white/80" x-text="$t('home.hero.subtitle')"></p>

                        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/70">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="car" :size="15" />
                                <span x-text="`${$number(showcase.length)} ${$t('home.hero.available')}`"></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="tag" :size="15" />
                                <span x-text="`${$number(s.brands.length)} ${$t('home.hero.brands')}`"></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="dollar" :size="15" />
                                <span x-text="`${$t('home.hero.fromPrice')} ${$money(cheapest)}`"></span>
                            </span>
                        </div>

                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            <a href="{{ url('/estoque') }}"
                                class="inline-flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                                <x-icon name="car" :size="16" />
                                <span x-text="$t('home.hero.cta')"></span>
                            </a>
                            <a href="{{ url('/financiamento') }}"
                                class="inline-flex h-11 items-center gap-2 rounded-lg border border-white/30 px-5 text-sm font-medium text-white transition hover:bg-white/10"
                                x-text="$t('home.hero.secondary')"></a>
                        </div>
                    </div>

                    {{-- ---- Busca rápida ---- --}}
                    <div class="min-w-0 rounded-2xl border border-white/15 bg-white/10 p-6 backdrop-blur">
                        <p class="text-xs tracking-wider text-white/60 uppercase" x-text="$t('home.quick.title')"></p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="mb-1 block text-xs text-white/70" for="q-brand" x-text="$t('home.quick.brand')"></label>
                                <select id="q-brand" x-model="brandId"
                                    class="focus-ring w-full rounded-lg border border-white/20 bg-transparent px-3 py-2.5 text-sm text-white">
                                    <option value="" class="text-gray-900" x-text="$t('home.quick.anyBrand')"></option>
                                    <template x-for="b in s.brands" :key="b.id">
                                        <option :value="b.id" class="text-gray-900" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs text-white/70" for="q-cat" x-text="$t('home.quick.category')"></label>
                                <select id="q-cat" x-model="category"
                                    class="focus-ring w-full rounded-lg border border-white/20 bg-transparent px-3 py-2.5 text-sm text-white">
                                    <option value="" class="text-gray-900" x-text="$t('home.quick.anyCategory')"></option>
                                    <template x-for="c in categories" :key="c.key">
                                        <option :value="c.key" class="text-gray-900" x-text="$t('spec.category.' + c.key)"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs text-white/70" for="q-price" x-text="$t('home.quick.priceMax')"></label>
                                <select id="q-price" x-model="priceMax"
                                    class="focus-ring w-full rounded-lg border border-white/20 bg-transparent px-3 py-2.5 text-sm text-white">
                                    <option value="" class="text-gray-900" x-text="$t('home.quick.anyPrice')"></option>
                                    <template x-for="p in priceSteps" :key="p">
                                        <option :value="p" class="text-gray-900" x-text="$money(p)"></option>
                                    </template>
                                </select>
                            </div>

                            <button type="button" @click="search()"
                                class="focus-ring flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 text-sm font-medium text-white transition hover:bg-brand-600">
                                <x-icon name="search" :size="16" />
                                <span x-text="$t('home.quick.cta')"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ---- Destaques da semana ---- --}}
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-title-sm font-bold text-heading" x-text="$t('home.highlights.title')"></h2>
                    <p class="mt-1 text-sm text-muted" x-text="$t('home.highlights.subtitle')"></p>
                </div>
                <a href="{{ url('/estoque') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-500 hover:text-brand-600">
                    <span x-text="$t('common.viewAll')"></span>
                    <x-icon name="arrow-right" :size="15" />
                </a>
            </div>

            <template x-if="highlights.length">
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="v in highlights" :key="v.id">
                        <div><x-dl.vehicle-card vehicle="v" /></div>
                    </template>
                </div>
            </template>
            <template x-if="!highlights.length">
                <div class="surface-card mt-6 rounded-2xl border">
                    <x-ui.empty-state icon="car" title="$t('stock.empty')" description="$t('stock.emptyDesc')" />
                </div>
            </template>
        </section>

        {{-- ---- Chegou agora ---- --}}
        <section class="border-y border-themed bg-gray-50 py-14 dark:bg-gray-900/40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-title-sm font-bold text-heading" x-text="$t('home.arrivals.title')"></h2>
                <p class="mt-1 text-sm text-muted" x-text="$t('home.arrivals.subtitle')"></p>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="v in arrivals" :key="v.id">
                        <div><x-dl.vehicle-card vehicle="v" compact="true" /></div>
                    </template>
                </div>
            </div>
        </section>

        {{-- ---- Por categoria ---- --}}
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h2 class="text-title-sm font-bold text-heading" x-text="$t('home.categories.title')"></h2>
            <p class="mt-1 text-sm text-muted" x-text="$t('home.categories.subtitle')"></p>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <template x-for="c in categories" :key="c.key">
                    <a :href="'{{ url('/estoque') }}?segmento=' + c.key"
                        class="surface-card group flex items-center gap-4 rounded-2xl border p-5 transition hover:shadow-theme-md">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 transition group-hover:bg-brand-500 group-hover:text-white dark:bg-brand-500/15">
                            <x-ui.icon-dyn expr="c.icon" :names="['car', 'truck', 'package']" :size="20" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-heading" x-text="$t('spec.category.' + c.key)"></span>
                            <span class="block text-xs text-muted"
                                x-text="$t('common.vehiclesCount', { count: $q.countByCategory(showcase, s.models, c.key) })"></span>
                        </span>
                    </a>
                </template>
            </div>
        </section>

        {{-- ---- Por que comprar aqui ---- --}}
        <section class="border-t border-themed bg-gray-50 py-14 dark:bg-gray-900/40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-title-sm font-bold text-heading" x-text="$t('home.why.title')"></h2>
                <p class="mt-1 text-sm text-muted" x-text="$t('home.why.subtitle')"></p>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <template x-for="item in why" :key="item.key">
                        <div class="surface-card rounded-2xl border p-5">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/15">
                                <x-ui.icon-dyn expr="item.icon" :names="['calculator', 'refresh-cw', 'calendar']" :size="20" />
                            </span>
                            <h3 class="mt-4 font-semibold text-heading" x-text="$t('home.why.' + item.key + '.title')"></h3>
                            <p class="mt-1.5 text-sm text-muted" x-text="$t('home.why.' + item.key + '.text')"></p>
                        </div>
                    </template>
                </div>

                {{-- ---- Chamada da troca ---- --}}
                <div class="surface-card mt-10 flex flex-col items-start justify-between gap-5 rounded-2xl border p-6 sm:flex-row sm:items-center md:p-8">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-heading" x-text="$t('home.sell.title')"></h3>
                        <p class="mt-1 max-w-xl text-sm text-muted" x-text="$t('home.sell.text')"></p>
                    </div>
                    <a href="{{ url('/vender-meu-carro') }}"
                        class="inline-flex h-11 shrink-0 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <x-icon name="calculator" :size="16" />
                        <span x-text="$t('home.sell.cta')"></span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layout.public>
