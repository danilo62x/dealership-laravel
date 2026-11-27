{{-- Veículo — porte de pages/public/VehiclePage.tsx. --}}
@php
    $crumbs = [
        ['href' => url('/'), 'expr' => "\$t('nav.home')", 'icon' => 'home'],
        ['href' => url('/estoque'), 'expr' => "\$t('nav.stock')"],
        ['expr' => 'label'],
    ];
@endphp
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{
            slug: @js($slug),
            get s() { return $store.dealer },
            get vehicle() { return $q.vehicleBySlug(this.s.vehicles, this.slug) },
            get label() { return this.vehicle ? $q.vehicleLabel(this.s.models, this.s.brands, this.vehicle) : $t('vehicle.notFound') },
            get days() { return this.vehicle ? $q.daysInStock(this.vehicle, $store.clock.now) : 0 },
            get similar() { return this.vehicle ? $q.similarVehicles(this.s.vehicles, this.s.models, this.vehicle, 4) : [] },
        }">
        <template x-if="!vehicle">
            <div class="mx-auto max-w-3xl py-16">
                <div class="surface-card rounded-2xl border">
                    <x-ui.empty-state icon="car" title="$t('vehicle.notFound')" description="$t('vehicle.notFoundDesc')">
                        <a href="{{ url('/estoque') }}"
                            class="focus-ring rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                            x-text="$t('nav.stock')"></a>
                    </x-ui.empty-state>
                </div>
            </div>
        </template>

        <template x-if="vehicle">
            <div>
                <x-ui.breadcrumb :items="$crumbs" />

                <div class="mt-5 grid gap-8 lg:grid-cols-[1.4fr_1fr]">
                    <div class="min-w-0">
                        <x-dl.vehicle-gallery seeds="vehicle.photoSeeds" alt="label" />

                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-heading" x-text="$t('vehicle.specs')"></h2>
                            <div class="mt-3">
                                <x-dl.spec-grid vehicle="vehicle" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-heading" x-text="$t('vehicle.options')"></h2>
                            <template x-if="vehicle.options.length">
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="opt in vehicle.options" :key="opt">
                                        <li class="inline-flex items-center gap-1.5 rounded-full border border-themed px-3 py-1.5 text-xs font-medium text-body">
                                            <span class="text-success-500"><x-icon name="check" :size="12" :strokeWidth="2.5" /></span>
                                            <span x-text="opt"></span>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <template x-if="!vehicle.options.length">
                                <p class="mt-3 text-sm text-muted" x-text="$t('vehicle.noOptions')"></p>
                            </template>
                        </div>
                    </div>

                    {{-- ---- Coluna de compra ---- --}}
                    <aside class="min-w-0 space-y-5">
                        <div class="surface-card rounded-2xl border p-5 md:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-dl.stock-pill state="vehicle.state" />
                                <x-dl.fuel-badge fuel="vehicle.fuel" />
                            </div>
                            <h1 class="mt-3 text-2xl font-bold text-heading" x-text="label"></h1>
                            <p class="mt-1 text-sm text-muted"
                                x-text="`${$q.yearLabel(vehicle)} · ${$number(vehicle.km)} km · ${vehicle.color}`"></p>

                            <p class="mt-5 text-title-sm font-bold text-brand-600 dark:text-brand-400" x-text="$money(vehicle.price)"></p>

                            <dl class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2">
                                    <dt class="text-muted" x-text="$t('vehicle.arrived')"></dt>
                                    <dd class="font-medium text-heading" x-text="$date(vehicle.arrivedAt)"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2">
                                    <dt class="text-muted" x-text="$t('vehicle.daysInStock')"></dt>
                                    <dd class="font-medium text-heading" x-text="$t('common.daysCount', { count: days })"></dd>
                                </div>
                            </dl>

                            <template x-if="vehicle.state === 'preparacao'">
                                <p class="mt-4 flex items-start gap-2 rounded-xl border border-warning-200 bg-warning-50 p-3 text-xs text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-500">
                                    <x-icon name="alert-triangle" :size="14" class="mt-0.5" />
                                    <span x-text="$t('vehicle.inPrep')"></span>
                                </p>
                            </template>

                            <div class="mt-5 flex flex-col gap-2">
                                <a :href="'{{ url('/test-drive') }}?veiculo=' + vehicle.id"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 text-sm font-medium text-white transition hover:bg-brand-600">
                                    <x-icon name="steering" :size="16" />
                                    <span x-text="$t('vehicle.scheduleDrive')"></span>
                                </a>
                                <button type="button" @click="$store.ui.notifySuccess($t('vehicle.offerSent'))"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg border border-themed text-sm font-medium text-body transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <x-icon name="handshake" :size="16" />
                                    <span x-text="$t('vehicle.makeOffer')"></span>
                                </button>
                            </div>
                        </div>

                        {{-- ---- Simulador embutido ---- --}}
                        <div>
                            <h2 class="mb-3 flex items-center gap-2 text-lg font-semibold text-heading">
                                <x-icon name="calculator" :size="18" class="text-brand-500" />
                                <span x-text="$t('vehicle.simulate')"></span>
                            </h2>
                            <x-dl.finance-simulator price="vehicle.price" :showMemo="true" />
                        </div>
                    </aside>
                </div>

                {{-- ---- Parecidos ---- --}}
                <template x-if="similar.length > 0">
                    <section class="mt-14">
                        <h2 class="text-title-sm font-bold text-heading" x-text="$t('vehicle.similar')"></h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <template x-for="v in similar" :key="v.id">
                                <div><x-dl.vehicle-card vehicle="v" compact="true" /></div>
                            </template>
                        </div>
                    </section>
                </template>
            </div>
        </template>
    </div>
</x-layout.public>
