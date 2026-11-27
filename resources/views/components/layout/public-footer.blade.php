{{-- Rodapé do site público — porte de components/layout/PublicFooter.tsx. --}}
@php
    $store = \App\Support\DlData::store();
    $categories = ['hatch', 'sedan', 'suv', 'picape'];
@endphp
<footer class="surface-card border-t border-themed">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-8 lg:flex-row">
            <div class="max-w-sm min-w-0">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white">
                        <x-icon name="car" :size="18" />
                    </span>
                    <span class="text-lg font-bold text-heading" x-text="$t('brand')"></span>
                </a>
                <p class="mt-4 text-sm text-muted" x-text="$t('footer.tagline')"></p>
                <p class="mt-4 text-xs text-muted">{{ $store['address'] }}</p>
                <p class="text-xs text-muted">{{ $store['phone'] }} · {{ $store['hours'] }}</p>
            </div>

            <div class="flex min-w-0 flex-wrap gap-x-10 gap-y-2 text-sm">
                <a href="{{ url('/estoque') }}" class="text-muted transition hover:text-brand-600" x-text="$t('nav.stock')"></a>
                @foreach ($categories as $category)
                    <a href="{{ url('/estoque?segmento=' . $category) }}" class="text-muted transition hover:text-brand-600"
                        x-text="$t('spec.category.{{ $category }}')"></a>
                @endforeach
                <a href="{{ url('/financiamento') }}" class="text-muted transition hover:text-brand-600" x-text="$t('nav.finance')"></a>
                <a href="{{ url('/test-drive') }}" class="text-muted transition hover:text-brand-600" x-text="$t('nav.testDrive')"></a>
                <a href="{{ url('/vender-meu-carro') }}" class="text-muted transition hover:text-brand-600" x-text="$t('nav.sellCar')"></a>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-themed pt-6 sm:flex-row">
            <p class="text-xs text-muted" x-text="`© {{ date('Y') }} ${$t('brand')}. ${$t('footer.rights')}`"></p>
            <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-muted">
                <a href="{{ url('/vender-meu-carro') }}" class="hover:text-brand-500" x-text="$t('footer.notice')"></a>
                <a href="#" class="hover:text-brand-500" x-text="$t('footer.privacy')"></a>
                <a href="#" class="hover:text-brand-500" x-text="$t('footer.terms')"></a>
            </div>
        </div>
    </div>
</footer>
