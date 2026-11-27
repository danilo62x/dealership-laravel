{{-- Barra lateral do showroom — porte de components/layout/AppSidebar.tsx.

     O contador ao lado de "Estoque" é o giro lento: veículos disponíveis
     parados há mais dias que `slowStockDays`. Sai da regra, nunca de um
     número fixo, e por isso lê o relógio global. --}}
@php $sections = $menu->appSections(); @endphp
<aside x-data="{
        get slow() {
            const s = $store.dealer;
            return $q.slowMovers(s.vehicles, $store.clock.now, s.rules).length;
        },
    }"
    :style="`width: ${$store.ui.sidebarCollapsed ? '90px' : '280px'}`"
    class="surface-sidebar fixed top-0 left-0 z-50 flex h-screen flex-col overflow-y-hidden border-r px-4 transition-[transform,width] duration-300 lg:static lg:translate-x-0"
    :class="$store.ui.sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex items-center pt-7 pb-6" :class="$store.ui.sidebarCollapsed ? 'justify-center' : 'justify-between'">
        <a href="{{ url('/app') }}" class="flex min-w-0 items-center gap-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-500 text-white shadow-theme-sm">
                <x-icon name="car" :size="18" />
            </span>
            <template x-if="!$store.ui.sidebarCollapsed">
                <span data-brand-text class="truncate text-lg font-semibold" x-text="$t('brand')"></span>
            </template>
        </a>
        <template x-if="!$store.ui.sidebarCollapsed">
            <button type="button" @click="$store.ui.toggleSidebar()" :title="$t('appNav.collapse')"
                class="is-sidebar-inactive hidden h-8 w-8 items-center justify-center rounded-lg border border-themed lg:flex">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </button>
        </template>
    </div>

    <div class="no-scrollbar flex flex-col gap-5 overflow-y-auto pb-4">
        @foreach ($sections as $section)
            <nav>
                <template x-if="!$store.ui.sidebarCollapsed">
                    <h3 class="mb-2 px-2 text-[11px] font-semibold tracking-wider uppercase" x-text="$t('{{ $section['titleKey'] }}')"></h3>
                </template>
                <ul class="flex flex-col gap-1">
                    @foreach ($section['items'] as $item)
                        <li>
                            <a href="{{ $item['to'] }}" @click="$store.ui.toggleSidebarMobile(false)"
                                :title="$store.ui.sidebarCollapsed ? $t('{{ $item['labelKey'] }}') : null"
                                class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $item['active'] ? 'is-sidebar-active' : 'is-sidebar-inactive' }}"
                                :class="$store.ui.sidebarCollapsed ? 'justify-center' : ''">
                                <x-icon :name="$item['icon']" :size="20" />
                                <template x-if="!$store.ui.sidebarCollapsed">
                                    <span class="min-w-0 flex-1 truncate" x-text="$t('{{ $item['labelKey'] }}')"></span>
                                </template>
                                @if (! empty($item['badge']))
                                    <template x-if="!$store.ui.sidebarCollapsed && slow > 0">
                                        <span class="rounded-full bg-warning-500 px-2 py-0.5 text-[11px] font-semibold text-white" x-text="slow"></span>
                                    </template>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endforeach
    </div>

    <div class="mt-auto space-y-3 border-t border-themed py-4">
        <template x-if="!$store.ui.sidebarCollapsed">
            <div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-500/10">
                <p class="text-xs font-medium text-muted" x-text="$t('appNav.slowTitle')"></p>
                <p class="mt-0.5 text-sm font-semibold text-heading" x-text="$t('appNav.slowDesc', { count: slow })"></p>
                <a href="{{ url('/app/estoque?giro=lento') }}"
                    class="mt-3 flex h-9 w-full items-center justify-center rounded-lg bg-brand-500 text-xs font-medium text-white transition hover:bg-brand-600"
                    x-text="$t('appNav.openSlow')"></a>
            </div>
        </template>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" @click="$store.auth.logout()"
                :title="$store.ui.sidebarCollapsed ? $t('appNav.logout') : null"
                class="is-sidebar-inactive group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                :class="$store.ui.sidebarCollapsed ? 'justify-center' : ''">
                <x-icon name="logout" :size="20" />
                <template x-if="!$store.ui.sidebarCollapsed">
                    <span x-text="$t('appNav.logout')"></span>
                </template>
            </button>
        </form>
    </div>
</aside>
