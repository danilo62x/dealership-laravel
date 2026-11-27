{{-- Agendamento em três passos — porte de components/dl/DriveScheduler.tsx.

     O horário só é aceito quando está livre para o VEÍCULO e para o VENDEDOR —
     e a recusa diz qual dos dois bateu, separado da recusa por CNH. --}}
<div class="space-y-5"
    x-data="{
        step: 0,
        vehicleId: 0,
        sellerId: 0,
        dayOffset: 1,
        slot: '10:00',
        name: '',
        email: '',
        phone: '',
        cnhUntil: '',
        conflict: null,
        cnhRefused: false,
        booked: null,
        slots: window.DL.slots,
        days: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],

        init() {
            const initial = Number(new URLSearchParams(window.location.search).get('veiculo')) || 0;
            this.vehicleId = initial || this.showcase[0]?.id || 0;
            this.sellerId = $store.dealer.sellers[0]?.id || 0;
            if (initial) this.step = 1;
        },

        get s() { return $store.dealer },
        get showcase() { return this.s.vehicles.filter((v) => v.state === 'disponivel') },
        get date() { return $dayKey(this.dayOffset) },
        get vehicle() { return $q.vehicleById(this.s.vehicles, this.vehicleId) },
        get sellerName() { return $q.sellerById(this.s.sellers, this.sellerId)?.name ?? '—' },

        clashOf(from) {
            return $q.driveConflict(
                { vehicleId: this.vehicleId, sellerId: this.sellerId, customerId: 0, date: this.date, from, to: $q.plusHour(from) },
                this.s.drives
            );
        },
        /** Choques do slot escolhido, calculados ao vivo enquanto o visitante escolhe. */
        get liveClash() { return this.clashOf(this.slot) },
        busy(from) {
            const clash = this.clashOf(from);
            return Boolean(clash.vehicle || clash.seller);
        },
        get noSlots() { return this.slots.every((from) => this.busy(from)) },

        submit() {
            this.conflict = null;
            this.cnhRefused = false;

            const customer = this.s.saveCustomer({
                name: this.name || 'Visitante',
                email: this.email,
                phone: this.phone,
                cnhUntil: this.cnhUntil ? new Date(this.cnhUntil + 'T12:00:00').toISOString() : null,
            });

            const result = this.s.scheduleDrive({
                vehicleId: this.vehicleId,
                customerId: customer.id,
                sellerId: this.sellerId,
                date: this.date,
                from: this.slot,
                to: $q.plusHour(this.slot),
            });

            if (result.ok && result.drive) {
                this.booked = result.drive;
                $store.ui.notifySuccess($t('drive.success', { date: $date(this.date + 'T12:00:00'), time: this.slot }));
                return;
            }

            this.conflict = { vehicle: result.vehicleClash, seller: result.sellerClash };
            this.cnhRefused = !result.cnhOk;
        },
    }">
    {{-- ---- Confirmado ---- --}}
    <template x-if="booked">
        <div class="surface-card rounded-2xl border p-6 text-center md:p-8">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-success-50 text-success-600 dark:bg-success-500/15">
                <x-icon name="check-circle" :size="30" />
            </span>
            <h3 class="mt-5 text-lg font-semibold text-heading"
                x-text="$t('drive.success', { date: $date(booked.date + 'T12:00:00'), time: booked.from })"></h3>
            <dl class="mx-auto mt-6 max-w-sm space-y-2.5 text-left text-sm">
                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                    <dt class="text-muted" x-text="$t('common.vehicle')"></dt>
                    <dd class="font-medium text-heading"
                        x-text="$q.vehicleLabel(s.models, s.brands, $q.vehicleById(s.vehicles, booked.vehicleId))"></dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                    <dt class="text-muted" x-text="$t('common.seller')"></dt>
                    <dd class="font-medium text-heading" x-text="$q.sellerById(s.sellers, booked.sellerId)?.name ?? '—'"></dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                    <dt class="text-muted" x-text="$t('common.customer')"></dt>
                    <dd class="font-medium text-heading" x-text="$q.customerById(s.customers, booked.customerId)?.name ?? '—'"></dd>
                </div>
            </dl>
        </div>
    </template>

    <template x-if="!booked">
        <div class="space-y-5">
            {{-- ---- Passos ---- --}}
            <ol class="grid gap-3 sm:grid-cols-3">
                <template x-for="(label, i) in [$t('drive.step1'), $t('drive.step2'), $t('drive.step3')]" :key="i">
                    <li class="flex items-center gap-3 rounded-xl border p-3.5"
                        :class="step === i ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-themed'">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm font-bold"
                            :class="step >= i ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800'"
                            x-text="i + 1"></span>
                        <span class="text-sm font-medium text-heading" x-text="label"></span>
                    </li>
                </template>
            </ol>

            <div class="surface-card rounded-2xl border p-5 md:p-6">
                {{-- ---- 1. Veículo ---- --}}
                <div x-show="step === 0">
                    <h3 class="font-semibold text-heading" x-text="$t('drive.chooseVehicle')"></h3>
                    <div class="mt-4 grid max-h-96 gap-3 overflow-y-auto sm:grid-cols-2">
                        <template x-for="v in showcase.slice(0, 24)" :key="v.id">
                            <button type="button" @click="vehicleId = v.id"
                                class="flex items-center gap-3 rounded-xl border p-3 text-left transition"
                                :class="vehicleId === v.id ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-themed hover:border-brand-300'">
                                <img :src="$img(v.photoSeeds?.[0] || v.slug, 160, 120)" alt="" loading="lazy"
                                    class="h-12 w-16 shrink-0 rounded-lg object-cover" />
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-heading"
                                        x-text="$q.vehicleLabel(s.models, s.brands, v)"></span>
                                    <span class="block text-xs text-muted" x-text="v.modelYear"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="step = 1"
                        class="focus-ring mt-5 flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                        <span x-text="$t('drive.nextStep')"></span>
                        <x-icon name="arrow-right" :size="16" />
                    </button>
                </div>

                {{-- ---- 2. Dia, vendedor e horário ---- --}}
                <div x-show="step === 1" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="dr-seller" x-text="$t('drive.chooseSeller')"></label>
                        <select id="dr-seller" x-model.number="sellerId"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                            <template x-for="seller in s.sellers" :key="seller.id">
                                <option :value="seller.id" x-text="seller.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('drive.chooseDay')"></span>
                        <div class="no-scrollbar flex gap-2 overflow-x-auto pb-1">
                            <template x-for="offset in days" :key="offset">
                                <button type="button" @click="dayOffset = offset"
                                    class="shrink-0 rounded-xl border px-3.5 py-2.5 text-center transition"
                                    :class="dayOffset === offset ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body'">
                                    <span class="block text-xs opacity-80"
                                        x-text="offset === 0 ? $t('common.today') : (offset === 1 ? $t('common.tomorrow') : '')"></span>
                                    <span class="block text-sm font-semibold" x-text="$date($dayKey(offset) + 'T12:00:00')"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('drive.chooseSlot')"></span>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="from in slots" :key="from">
                                <button type="button" :disabled="busy(from)" @click="slot = from"
                                    :title="busy(from) ? $t('drive.slotTaken') : null"
                                    class="focus-ring rounded-lg border px-3.5 py-2 text-sm font-medium transition"
                                    :class="busy(from)
                                        ? 'cursor-not-allowed border-themed text-muted line-through opacity-50'
                                        : (slot === from ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body hover:text-heading')"
                                    x-text="from"></button>
                            </template>
                        </div>
                        <template x-if="noSlots">
                            <p class="mt-2 text-xs text-error-600 dark:text-error-500" x-text="$t('drive.noSlots')"></p>
                        </template>
                    </div>

                    {{-- Aviso ao vivo: os dois choques aparecem separados. --}}
                    <template x-if="liveClash.vehicle || liveClash.seller">
                        <div class="rounded-xl border border-warning-200 bg-warning-50 p-3.5 text-sm dark:border-warning-500/30 dark:bg-warning-500/10">
                            <template x-if="liveClash.vehicle">
                                <p class="flex items-start gap-2 text-warning-700 dark:text-warning-500">
                                    <x-icon name="car" :size="15" class="mt-0.5" />
                                    <span x-text="$t('drive.conflictVehicle', { time: liveClash.vehicle.from + '–' + liveClash.vehicle.to })"></span>
                                </p>
                            </template>
                            <template x-if="liveClash.seller">
                                <p class="mt-1.5 flex items-start gap-2 text-warning-700 dark:text-warning-500">
                                    <x-icon name="user" :size="15" class="mt-0.5" />
                                    <span x-text="$t('drive.conflictSeller', { name: $q.sellerById(s.sellers, liveClash.seller.sellerId)?.name ?? '—' })"></span>
                                </p>
                            </template>
                        </div>
                    </template>
                    <template x-if="!liveClash.vehicle && !liveClash.seller">
                        <p class="flex items-center gap-2 text-sm text-success-600 dark:text-success-500">
                            <x-icon name="check-circle" :size="15" />
                            <span x-text="$t('drive.bothFree')"></span>
                        </p>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="step = 0"
                            class="focus-ring flex h-11 items-center rounded-lg border border-themed px-5 text-sm font-medium text-body"
                            x-text="$t('drive.back')"></button>
                        <button type="button" @click="step = 2"
                            class="focus-ring flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <span x-text="$t('drive.nextStep')"></span>
                            <x-icon name="arrow-right" :size="16" />
                        </button>
                    </div>
                </div>

                {{-- ---- 3. Dados e CNH ---- --}}
                <div x-show="step === 2" class="space-y-4">
                    <div class="rounded-xl border border-themed p-3.5 text-sm">
                        <p class="font-medium text-heading" x-text="vehicle ? $q.vehicleLabel(s.models, s.brands, vehicle) : '—'"></p>
                        <p class="text-xs text-muted"
                            x-text="`${$date(date + 'T12:00:00')} · ${slot}–${$q.plusHour(slot)} · ${sellerName}`"></p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="dr-name" x-text="$t('drive.yourName')"></label>
                            <input id="dr-name" x-model="name"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="dr-phone" x-text="$t('drive.yourPhone')"></label>
                            <input id="dr-phone" x-model="phone"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="dr-email" x-text="$t('drive.yourEmail')"></label>
                            <input id="dr-email" type="email" x-model="email"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="dr-cnh" x-text="$t('drive.cnh')"></label>
                            <input id="dr-cnh" type="date" x-model="cnhUntil"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                            <p class="mt-1 text-xs text-muted" x-text="$t('drive.cnhHint')"></p>
                        </div>
                    </div>

                    <template x-if="conflict?.vehicle || conflict?.seller || cnhRefused">
                        <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-sm dark:border-error-500/30 dark:bg-error-500/10">
                            <p class="font-semibold text-error-700 dark:text-error-500" x-text="$t('drive.refusedTitle')"></p>
                            <ul class="mt-2 space-y-1.5 text-error-600 dark:text-error-500">
                                <template x-if="conflict?.vehicle">
                                    <li class="flex items-start gap-2">
                                        <x-icon name="car" :size="15" class="mt-0.5" />
                                        <span x-text="$t('drive.conflictVehicle', { time: conflict.vehicle.from + '–' + conflict.vehicle.to })"></span>
                                    </li>
                                </template>
                                <template x-if="conflict?.seller">
                                    <li class="flex items-start gap-2">
                                        <x-icon name="user" :size="15" class="mt-0.5" />
                                        <span x-text="$t('drive.conflictSeller', { name: $q.sellerById(s.sellers, conflict.seller.sellerId)?.name ?? '—' })"></span>
                                    </li>
                                </template>
                                <template x-if="cnhRefused">
                                    <li class="flex items-start gap-2">
                                        <x-icon name="ban" :size="15" class="mt-0.5" />
                                        <span x-text="$t('drive.conflictCnh')"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="step = 1"
                            class="focus-ring flex h-11 items-center rounded-lg border border-themed px-5 text-sm font-medium text-body"
                            x-text="$t('drive.back')"></button>
                        <button type="button" @click="submit()"
                            class="focus-ring flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <x-icon name="calendar" :size="16" />
                            <span x-text="$t('drive.confirm')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
