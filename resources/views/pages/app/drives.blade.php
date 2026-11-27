{{-- Test-drives — porte de pages/app/DrivesPage.tsx.
     A agenda por dia mostra veículo e vendedor; o choque do veículo e o do
     vendedor são apontados SEPARADAMENTE. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('drives.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="drivesPage()">
        <x-app.page-header title="$t('drives.title')" subtitle="$t('drives.subtitle')" :crumbs="$crumbs">
            <x-slot:action>
                <template x-if="conflictCount > 0">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-error-50 px-4 py-2.5 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <x-icon name="alert-triangle" :size="16" />
                        <span x-text="$t('drives.conflictsCount', { count: conflictCount })"></span>
                    </span>
                </template>
            </x-slot:action>
        </x-app.page-header>

        {{-- ---- Régua de dias ---- --}}
        <div class="surface-card no-scrollbar mb-5 flex gap-2 overflow-x-auto rounded-2xl border p-3">
            <template x-for="o in days" :key="o">
                <button type="button" @click="offset = o" class="shrink-0 rounded-xl border px-3.5 py-2.5 text-center transition"
                    :class="offset === o ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body hover:border-brand-300'">
                    <span class="block text-[11px] opacity-80"
                        x-text="o === 0 ? $t('common.today') : (o === 1 ? $t('common.tomorrow') : '')"></span>
                    <span class="block text-sm font-semibold" x-text="$date($dayKey(o) + 'T12:00:00')"></span>
                    <span class="block text-[11px] opacity-80" x-text="dayCount(o)"></span>
                </button>
            </template>
        </div>

        <template x-if="rows.length">
            <div class="surface-card overflow-hidden rounded-2xl border">
                <ul class="divide-y divide-[color:var(--tx-border)]">
                    <template x-for="drive in rows" :key="drive.id">
                        <li class="p-4" :class="clashOf(drive) ? 'bg-error-50/60 dark:bg-error-500/5' : ''">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="w-24 shrink-0 text-sm font-semibold text-heading" x-text="`${drive.from}–${drive.to}`"></span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-heading">
                                        <a :href="'{{ url('/app/veiculo') }}/' + drive.vehicleId" class="hover:text-brand-600"
                                            x-text="$q.vehicleLabel(s.models, s.brands, $q.vehicleById(s.vehicles, drive.vehicleId))"></a>
                                    </p>
                                    <p class="truncate text-xs text-muted" x-text="$q.customerById(s.customers, drive.customerId)?.name ?? '—'"></p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <x-ui.avatar src="$q.sellerById(s.sellers, drive.sellerId)?.avatar" name="$q.sellerById(s.sellers, drive.sellerId)?.name" size="xs" />
                                    <span class="hidden text-xs text-muted sm:block" x-text="$q.sellerById(s.sellers, drive.sellerId)?.name ?? '—'"></span>
                                </div>

                                <template x-if="!drive.cnhOk">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                        <x-icon name="ban" :size="11" />
                                        <span x-text="$t('drives.cnhMissing')"></span>
                                    </span>
                                </template>

                                <x-dl.drive-status-pill status="drive.status" />

                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" :title="$t('drives.markDone')" @click="setStatus(drive.id, 'realizado')"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-themed text-muted hover:text-success-600">
                                        <x-icon name="check" :size="15" />
                                    </button>
                                    <button type="button" :title="$t('drives.markNoShow')" @click="setStatus(drive.id, 'faltou')"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-themed text-muted hover:text-warning-600">
                                        <x-icon name="user" :size="15" />
                                    </button>
                                    <button type="button" :title="$t('drives.cancelDrive')" @click="setStatus(drive.id, 'cancelado')"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-themed text-muted hover:text-error-600">
                                        <x-icon name="x" :size="15" />
                                    </button>
                                </div>
                            </div>

                            {{-- Os dois conflitos aparecem separados: veículo e vendedor. --}}
                            <template x-if="clashOf(drive)">
                                <div class="mt-3 flex flex-wrap gap-2 pl-0 sm:pl-24">
                                    <template x-if="clashOf(drive).vehicle">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-2.5 py-1 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                            <x-icon name="car" :size="12" />
                                            <span x-text="`${$t('drives.conflictVehicle')} · ${clashOf(drive).vehicle.from}–${clashOf(drive).vehicle.to}`"></span>
                                        </span>
                                    </template>
                                    <template x-if="clashOf(drive).seller">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-500">
                                            <x-icon name="user" :size="12" />
                                            <span x-text="`${$t('drives.conflictSeller')} · ${$q.sellerById(s.sellers, clashOf(drive).seller.sellerId)?.name ?? '—'}`"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <template x-if="!rows.length">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="calendar" title="$t('drives.empty')" description="$t('drives.emptyDesc')" />
            </div>
        </template>
    </div>

    @push('scripts')
        <script>
            function drivesPage() {
                return {
                    offset: 0,
                    days: Array.from({ length: 14 }, (_, i) => i - 3),

                    get s() { return $store.dealer },
                    get date() { return $dayKey(this.offset) },
                    get rows() { return $q.drivesOfDay(this.s.drives, this.date) },
                    /** Os choques do dia, apontados separadamente para veículo e vendedor. */
                    get conflicts() {
                        const map = {};
                        for (const drive of this.rows) {
                            const clash = $q.driveConflict(drive, this.s.drives);
                            if (clash.vehicle || clash.seller) map[drive.id] = clash;
                        }
                        return map;
                    },
                    get conflictCount() { return Object.keys(this.conflicts).length },
                    clashOf(drive) { return this.conflicts[drive.id] ?? null },
                    dayCount(o) { return $q.drivesOfDay(this.s.drives, $dayKey(o)).length },
                    setStatus(id, status) {
                        this.s.setDriveStatus(id, status);
                        $store.ui.notifySuccess($t('drives.updated'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
