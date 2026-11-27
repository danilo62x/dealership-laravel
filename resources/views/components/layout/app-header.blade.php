{{-- Topo do showroom — porte de components/layout/AppHeader.tsx.
     Os avisos são derivados dos próprios dados, nunca de uma lista fixa:
     test-drives de hoje, propostas mais novas e o veículo mais parado. --}}
<header x-data="{
        dropdownOpen: false,
        notifOpen: false,
        get notifications() {
            const now = $store.clock.now;
            const s = $store.dealer;
            const items = [];
            for (const drive of $q.drivesOfDay(s.drives, window.TODAY_KEY).slice(0, 2)) {
                const vehicle = $q.vehicleById(s.vehicles, drive.vehicleId);
                if (!vehicle) continue;
                items.push({
                    id: 'drive-' + drive.id,
                    text: $t('notifications.driveToday', { name: $q.vehicleLabel(s.models, s.brands, vehicle) }),
                    time: drive.from + '–' + drive.to,
                });
            }
            for (const deal of [...s.deals].sort((a, b) => b.createdAt.localeCompare(a.createdAt)).slice(0, 2)) {
                const vehicle = $q.vehicleById(s.vehicles, deal.vehicleId);
                if (!vehicle) continue;
                items.push({
                    id: 'deal-' + deal.id,
                    text: $t('notifications.newDeal', { name: $q.vehicleLabel(s.models, s.brands, vehicle) }),
                    time: $date(deal.createdAt),
                });
            }
            for (const row of $q.slowMovers(s.vehicles, now, s.rules).slice(0, 1)) {
                items.push({
                    id: 'slow-' + row.vehicle.id,
                    text: $t('notifications.slowStock', { name: $q.vehicleLabel(s.models, s.brands, row.vehicle) }),
                    time: $t('common.daysCount', { count: row.days }),
                });
            }
            return items;
        },
        toggleSidebar() {
            if (window.innerWidth < 1024) $store.ui.toggleSidebarMobile();
            else $store.ui.toggleSidebar();
        },
    }"
    class="surface-header sticky top-0 z-30 flex w-full border-b">
    <div class="flex flex-grow items-center justify-between gap-4 px-4 py-3 md:px-6 2xl:px-10">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="toggleSidebar()" aria-label="Menu"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-themed text-muted hover:bg-gray-50 dark:hover:bg-gray-800">
                <x-icon name="menu" :size="20" />
            </button>

            <a href="{{ url('/app/estoque?novo=1') }}"
                class="hidden h-10 items-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600 sm:flex">
                <x-icon name="plus" :size="18" />
                <span x-text="$t('appNav.newVehicle')"></span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}"
                class="hidden h-10 items-center gap-2 rounded-lg border border-themed px-4 text-sm text-muted transition hover:bg-gray-50 md:flex dark:hover:bg-gray-800">
                <x-icon name="globe" :size="16" />
                <span x-text="$t('appNav.viewSite')"></span>
            </a>
            <x-ui.language-switcher />
            <button type="button" @click="$store.ui.toggleDarkMode()" :aria-label="$t('common.theme')"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-themed text-muted hover:bg-gray-50 dark:hover:bg-gray-800">
                <x-ui.icon-dyn expr="$store.ui.darkMode ? 'sun' : 'moon'" :names="['sun', 'moon']" :size="18" />
            </button>

            <div class="relative" @click.outside="notifOpen = false">
                <button type="button" :aria-label="$t('appNav.notifications')" @click="notifOpen = !notifOpen"
                    class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-themed text-muted hover:bg-gray-50 dark:hover:bg-gray-800">
                    <x-icon name="bell" :size="18" />
                    <template x-if="notifications.length > 0">
                        <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-error-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-error-500"></span>
                        </span>
                    </template>
                </button>
                <div x-show="notifOpen" x-cloak
                    class="surface-card absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] rounded-xl border shadow-theme-lg">
                    <div class="flex items-center justify-between border-b border-themed px-4 py-3">
                        <p class="text-sm font-semibold text-heading" x-text="$t('appNav.notifications')"></p>
                    </div>
                    <ul class="max-h-72 divide-y divide-[color:var(--tx-border)] overflow-y-auto">
                        <template x-for="n in notifications" :key="n.id">
                            <li class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <span class="mt-1 flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-heading" x-text="n.text"></p>
                                    <p class="text-xs text-muted" x-text="n.time"></p>
                                </div>
                            </li>
                        </template>
                        <template x-if="notifications.length === 0">
                            <li class="px-4 py-6 text-center text-sm text-muted" x-text="$t('common.noResults')"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="relative" @click.outside="dropdownOpen = false">
                <button type="button" class="ml-1 flex items-center gap-3" @click="dropdownOpen = !dropdownOpen">
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-heading" x-text="$store.auth.user?.name || '—'"></span>
                        <span class="block text-xs text-muted" x-text="$store.auth.user?.role || '—'"></span>
                    </span>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-500 font-semibold text-white"
                        x-text="$store.auth.initials"></span>
                    <x-icon name="chevron-down" :size="16" class="text-muted" />
                </button>

                <div x-show="dropdownOpen" x-cloak class="surface-card absolute right-0 mt-3 w-56 rounded-xl border shadow-theme-lg">
                    <div class="border-b border-themed px-4 py-3">
                        <p class="truncate text-sm font-medium text-heading" x-text="$store.auth.user?.name || '—'"></p>
                        <p class="truncate text-xs text-muted" x-text="$store.auth.user?.email || ''"></p>
                    </div>
                    <ul class="py-1">
                        <li>
                            <a href="{{ url('/app/configuracoes') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-body hover:bg-gray-50 dark:hover:bg-gray-800">
                                <x-icon name="settings" :size="16" />
                                <span x-text="$t('appNav.settings')"></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-body hover:bg-gray-50 dark:hover:bg-gray-800">
                                <x-icon name="globe" :size="16" />
                                <span x-text="$t('appNav.viewSite')"></span>
                            </a>
                        </li>
                    </ul>
                    <div class="border-t border-themed py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" @click="$store.auth.logout()"
                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10">
                                <x-icon name="logout" :size="16" />
                                <span x-text="$t('appNav.logout')"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
