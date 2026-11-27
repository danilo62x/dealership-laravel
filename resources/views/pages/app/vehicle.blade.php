{{-- Veículo do showroom — porte de pages/app/VehicleAdminPage.tsx.
     Abas: Ficha, Custos (aquisição, preparação, margem), Propostas e Test-drives. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['href' => url('/app/estoque'), 'expr' => "\$t('stockAdmin.title')"], ['expr' => 'label']];
    $tabs = [
        ['label' => "\$t('vehicleAdmin.tabs.spec')", 'icon' => 'file-text'],
        ['label' => "\$t('vehicleAdmin.tabs.costs')", 'icon' => 'dollar'],
        ['label' => "\$t('vehicleAdmin.tabs.deals')", 'icon' => 'handshake'],
        ['label' => "\$t('vehicleAdmin.tabs.drives')", 'icon' => 'steering'],
    ];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="vehicleAdminPage(@js($vehicleId))">
        <template x-if="!vehicle">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="car" title="$t('vehicle.notFound')" description="$t('vehicle.notFoundDesc')">
                    <a href="{{ url('/app/estoque') }}"
                        class="focus-ring rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white"
                        x-text="$t('vehicleAdmin.backToList')"></a>
                </x-ui.empty-state>
            </div>
        </template>

        <template x-if="vehicle">
            <div>
                <x-app.page-header title="label"
                    subtitle="`${$q.yearLabel(vehicle)} · ${$number(vehicle.km)} km · ${vehicle.color}`"
                    :crumbs="$crumbs">
                    <x-slot:action>
                        <a href="{{ url('/app/estoque') }}"
                            class="focus-ring flex h-11 items-center gap-2 rounded-lg border border-themed px-5 text-sm font-medium text-body">
                            <x-icon name="arrow-right" :size="16" class="rotate-180" />
                            <span x-text="$t('vehicleAdmin.backToList')"></span>
                        </a>
                    </x-slot:action>
                </x-app.page-header>

                <div class="surface-card rounded-2xl border p-5 md:p-6">
                    <x-ui.tabs model="tab" :tabs="$tabs" />

                    <div class="pt-5 text-sm text-body">
                        {{-- ---- Ficha ---- --}}
                        <div x-show="tab === 0" class="space-y-5">
                            <div class="grid gap-5 lg:grid-cols-[260px_1fr]">
                                <img :src="$img(vehicle.photoSeeds?.[0] || vehicle.slug, 520, 360)" :alt="label"
                                    class="aspect-[3/2] w-full rounded-2xl object-cover" />
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-dl.stock-pill state="vehicle.state" />
                                        <span class="text-xs text-muted" x-text="`${$q.yearLabel(vehicle)} · ${$number(vehicle.km)} km`"></span>
                                    </div>
                                    <p class="mt-3 text-title-sm font-bold text-heading" x-text="$money(vehicle.price)"></p>
                                    <p class="mt-1 text-sm text-muted"
                                        x-text="`${$t('vehicle.arrived')}: ${$date(vehicle.arrivedAt)} · ${$t('common.daysCount', { count: days })}`"></p>

                                    <div class="mt-5">
                                        <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('common.status')"></span>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="st in states" :key="st">
                                                <button type="button" @click="setState(st)"
                                                    class="focus-ring rounded-lg border px-3.5 py-2 text-sm font-medium transition"
                                                    :class="vehicle.state === st ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body'"
                                                    x-text="$t('status.stock.' + st)"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <x-dl.spec-grid vehicle="vehicle" />

                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-heading" x-text="$t('vehicle.options')"></h3>
                                <template x-if="vehicle.options.length">
                                    <ul class="flex flex-wrap gap-2">
                                        <template x-for="opt in vehicle.options" :key="opt">
                                            <li class="rounded-full border border-themed px-3 py-1.5 text-xs text-body" x-text="opt"></li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!vehicle.options.length">
                                    <p class="text-sm text-muted" x-text="$t('vehicle.noOptions')"></p>
                                </template>
                            </div>
                        </div>

                        {{-- ---- Custos ---- --}}
                        <div x-show="tab === 1" class="space-y-5">
                            <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-xl border border-themed p-4">
                                    <dt class="text-xs text-muted" x-text="$t('vehicleAdmin.costs.acquisition')"></dt>
                                    <dd class="mt-1 text-lg font-semibold text-heading" x-text="$money(vehicle.acquisitionCost)"></dd>
                                </div>
                                <div class="rounded-xl border border-themed p-4">
                                    <dt class="text-xs text-muted" x-text="$t('vehicleAdmin.costs.prep')"></dt>
                                    <dd class="mt-1 text-lg font-semibold text-heading" x-text="$money(vehicle.prepCost)"></dd>
                                </div>
                                <div class="rounded-xl border border-themed p-4">
                                    <dt class="text-xs text-muted" x-text="$t('vehicleAdmin.costs.totalCost')"></dt>
                                    <dd class="mt-1 text-lg font-semibold text-heading" x-text="$money(totalCost)"></dd>
                                </div>
                                <div class="rounded-xl border border-themed p-4">
                                    <dt class="text-xs text-muted" x-text="$t('vehicleAdmin.costs.price')"></dt>
                                    <dd class="mt-1 text-lg font-semibold text-heading" x-text="$money(vehicle.price)"></dd>
                                </div>
                            </dl>

                            <div class="grid gap-5 lg:grid-cols-2">
                                <div class="rounded-2xl border border-themed p-5">
                                    <h3 class="text-sm font-semibold text-heading" x-text="$t('vehicleAdmin.costs.margin')"></h3>
                                    <p class="mt-2 text-title-sm font-bold"
                                        :class="marginValue < 0 ? 'text-error-600 dark:text-error-500' : 'text-success-600 dark:text-success-500'"
                                        x-text="$money(marginValue)"></p>
                                    <p class="mt-1 text-sm text-muted"
                                        x-text="`${$t('vehicleAdmin.costs.marginPct')}: ${$percent($q.marginPct(vehicle, vehicle.price))}`"></p>
                                    <p class="mt-3 font-mono text-xs text-muted"
                                        x-text="`${$money(vehicle.price)} − ${$money(vehicle.acquisitionCost)} − ${$money(vehicle.prepCost)}`"></p>
                                    <template x-if="marginValue < 0">
                                        <p class="mt-3 flex items-center gap-2 text-xs text-error-600 dark:text-error-500">
                                            <x-icon name="alert-triangle" :size="14" />
                                            <span x-text="$t('vehicleAdmin.costs.loss')"></span>
                                        </p>
                                    </template>
                                </div>

                                <div class="rounded-2xl border border-themed p-5">
                                    <h3 class="text-sm font-semibold text-heading" x-text="$t('vehicleAdmin.costs.commission')"></h3>
                                    <p class="mt-2 text-title-sm font-bold text-brand-600 dark:text-brand-400"
                                        x-text="$money($q.commission(marginValue, s.rules))"></p>
                                    <p class="mt-1 text-sm text-muted"
                                        x-text="$t('vehicleAdmin.costs.commissionHint', { pct: $percent(rate) })"></p>

                                    <div class="mt-4 border-t border-themed pt-3">
                                        <p class="text-xs text-muted" x-text="$t('vehicle.daysInStock')"></p>
                                        <p class="mt-0.5 text-sm font-semibold text-heading" x-text="$t('common.daysCount', { count: days })"></p>
                                        <template x-if="suggested > 0">
                                            <p class="mt-1 text-xs text-warning-600 dark:text-warning-500"
                                                x-text="`${$t('dashboard.suggested')}: −${$percent(suggested)} = ${$money(Math.round((vehicle.price * (1 - suggested)) / 100) * 100)}`"></p>
                                        </template>
                                        <p class="mt-2 text-xs text-muted" x-text="$t('vehicleAdmin.costs.daysNote')"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-themed p-5">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-acq" x-text="$t('stockAdmin.form.acquisitionCost')"></label>
                                        <input id="c-acq" type="number" :value="vehicle.acquisitionCost"
                                            @input="s.saveVehicle({ id: vehicle.id, acquisitionCost: Number($event.target.value) })"
                                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-prep" x-text="$t('stockAdmin.form.prepCost')"></label>
                                        <input id="c-prep" type="number" :value="vehicle.prepCost"
                                            @input="s.saveVehicle({ id: vehicle.id, prepCost: Number($event.target.value) })"
                                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-price" x-text="$t('stockAdmin.form.price')"></label>
                                        <input id="c-price" type="number" :value="vehicle.price"
                                            @input="s.saveVehicle({ id: vehicle.id, price: Number($event.target.value) })"
                                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ---- Propostas ---- --}}
                        <div x-show="tab === 2">
                            <template x-if="vehicleDeals.length">
                                <ul class="divide-y divide-[color:var(--tx-border)]">
                                    <template x-for="deal in vehicleDeals" :key="deal.id">
                                        <li class="flex flex-wrap items-center gap-3 py-3">
                                            <x-dl.deal-stage-pill stage="deal.stage" />
                                            <a :href="'{{ url('/app/proposta') }}/' + deal.id"
                                                class="min-w-0 flex-1 text-sm font-medium text-heading hover:text-brand-600"
                                                x-text="$q.customerById(s.customers, deal.customerId)?.name ?? '—'"></a>
                                            <span class="text-xs text-muted" x-text="$q.sellerById(s.sellers, deal.sellerId)?.name ?? '—'"></span>
                                            <span class="text-sm font-semibold text-heading" x-text="$money($q.dealPrice(deal, vehicle))"></span>
                                            <span class="text-xs text-muted" x-text="$date(deal.movedAt)"></span>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <template x-if="!vehicleDeals.length">
                                <x-ui.empty-state icon="handshake" title="$t('vehicleAdmin.noDeals')" />
                            </template>
                        </div>

                        {{-- ---- Test-drives ---- --}}
                        <div x-show="tab === 3">
                            <template x-if="vehicleDrives.length">
                                <ul class="divide-y divide-[color:var(--tx-border)]">
                                    <template x-for="drive in vehicleDrives" :key="drive.id">
                                        <li class="flex flex-wrap items-center gap-3 py-3">
                                            <span class="w-28 shrink-0 text-sm font-medium text-heading" x-text="$date(drive.date + 'T12:00:00')"></span>
                                            <span class="w-24 shrink-0 text-sm text-muted" x-text="`${drive.from}–${drive.to}`"></span>
                                            <div class="flex min-w-0 flex-1 items-center gap-2">
                                                <x-ui.avatar src="$q.sellerById(s.sellers, drive.sellerId)?.avatar" name="$q.sellerById(s.sellers, drive.sellerId)?.name" size="xs" />
                                                <span class="truncate text-sm text-body" x-text="$q.customerById(s.customers, drive.customerId)?.name ?? '—'"></span>
                                            </div>
                                            <template x-if="!drive.cnhOk">
                                                <span class="rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500"
                                                    x-text="$t('drives.cnhMissing')"></span>
                                            </template>
                                            <x-dl.drive-status-pill status="drive.status" />
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <template x-if="!vehicleDrives.length">
                                <x-ui.empty-state icon="steering" title="$t('vehicleAdmin.noDrives')" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
        <script>
            function vehicleAdminPage(vehicleId) {
                return {
                    vehicleId,
                    tab: 0,
                    states: ['disponivel', 'reservado', 'vendido', 'preparacao'],

                    get s() { return $store.dealer },
                    get vehicle() { return $q.vehicleById(this.s.vehicles, this.vehicleId) },
                    get label() { return this.vehicle ? $q.vehicleLabel(this.s.models, this.s.brands, this.vehicle) : $t('vehicle.notFound') },
                    get days() { return this.vehicle ? $q.daysInStock(this.vehicle, $store.clock.now) : 0 },
                    get totalCost() { return this.vehicle ? this.vehicle.acquisitionCost + this.vehicle.prepCost : 0 },
                    get marginValue() { return this.vehicle ? $q.margin(this.vehicle, this.vehicle.price) : 0 },
                    get rate() { return $q.commissionRate(this.marginValue, this.s.rules) },
                    get suggested() { return $q.suggestedDiscountPct(this.days, this.s.rules) },
                    get vehicleDeals() { return this.vehicle ? $q.dealsOfVehicle(this.s.deals, this.vehicle.id) : [] },
                    get vehicleDrives() { return this.vehicle ? $q.drivesOfVehicle(this.s.drives, this.vehicle.id) : [] },

                    setState(state) {
                        this.s.setVehicleState(this.vehicleId, state);
                        $store.ui.notifySuccess($t('vehicleAdmin.saved'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
