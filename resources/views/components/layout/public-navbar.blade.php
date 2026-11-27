{{-- Barra do site público — porte de components/layout/PublicNavbar.tsx.
     O contador ao lado de "Estoque" é o tamanho da vitrine (disponível +
     preparação), derivado do store — nunca um número fixo. --}}
@php $links = $menu->publicLinks(); @endphp
<header x-data="{
        mobileOpen: false,
        scrolled: false,
        get availableCount() { return $q.availableStock($store.dealer.vehicles).length },
    }"
    @scroll.window="scrolled = window.scrollY > 8"
    class="sticky top-0 z-40 border-b transition-colors"
    :class="scrolled ? 'surface-header border-themed backdrop-blur' : 'border-transparent bg-transparent'">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="flex min-w-0 shrink-0 items-center gap-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-theme-md">
                <x-icon name="car" :size="18" />
            </span>
            <span class="truncate text-lg font-bold text-heading" x-text="$t('brand')"></span>
        </a>

        <nav class="ml-6 hidden items-center gap-1 lg:flex">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $link['active'] ? 'text-brand-600 dark:text-brand-400' : 'text-body hover:text-heading' }}">
                    <span x-text="$t('{{ $link['key'] }}')"></span>
                    @if (! empty($link['badge']))
                        <span x-show="availableCount > 0" x-cloak x-text="availableCount"
                            class="ml-1.5 rounded-full bg-brand-50 px-1.5 py-0.5 text-[11px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1 sm:gap-2">
            <button type="button" @click="$store.ui.toggleDarkMode()" :aria-label="$t('common.theme')"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-themed text-body transition hover:bg-gray-50 dark:hover:bg-gray-800">
                <x-ui.icon-dyn expr="$store.ui.darkMode ? 'sun' : 'moon'" :names="['sun', 'moon']" :size="18" />
            </button>
            <x-ui.language-switcher />

            <template x-if="$store.auth.isAuthenticated">
                <a href="{{ url('/app') }}"
                    class="hidden h-10 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600 sm:flex"
                    x-text="$t('nav.dashboard')"></a>
            </template>
            <template x-if="!$store.auth.isAuthenticated">
                <span class="contents">
                    <a href="{{ route('login') }}"
                        class="hidden h-10 items-center rounded-lg border border-themed px-4 text-sm font-medium text-body transition hover:bg-gray-50 sm:flex dark:hover:bg-gray-800"
                        x-text="$t('nav.login')"></a>
                    <a href="{{ route('register') }}"
                        class="hidden h-10 items-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600 sm:flex">
                        <x-icon name="user" :size="16" />
                        <span x-text="$t('nav.register')"></span>
                    </a>
                </span>
            </template>

            <button type="button" @click="mobileOpen = !mobileOpen" aria-label="Menu"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-body lg:hidden">
                <x-ui.icon-dyn expr="mobileOpen ? 'x' : 'menu'" :names="['x', 'menu']" :size="22" />
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak class="surface-card border-t border-themed lg:hidden">
        <nav class="flex flex-col gap-1 px-4 py-4 sm:px-6">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                    class="rounded-lg px-3 py-2.5 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800"
                    x-text="$t('{{ $link['key'] }}')"></a>
            @endforeach
            <div class="mt-2 flex flex-col gap-2 border-t border-themed pt-3">
                <template x-if="$store.auth.isAuthenticated">
                    <a href="{{ url('/app') }}"
                        class="flex h-11 items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white"
                        x-text="$t('nav.dashboard')"></a>
                </template>
                <template x-if="!$store.auth.isAuthenticated">
                    <span class="contents">
                        <a href="{{ route('login') }}"
                            class="flex h-11 items-center justify-center rounded-lg border border-themed text-sm font-medium text-body"
                            x-text="$t('nav.login')"></a>
                        <a href="{{ route('register') }}"
                            class="flex h-11 items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white"
                            x-text="$t('nav.register')"></a>
                    </span>
                </template>
            </div>
        </nav>
    </div>
</header>
