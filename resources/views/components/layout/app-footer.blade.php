{{-- Rodapé do showroom — porte de components/layout/AppFooter.tsx. --}}
<footer class="surface-header border-t px-4 py-4 md:px-6 2xl:px-10">
    <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
        <p class="text-xs text-muted" x-text="`© {{ date('Y') }} ${$t('brand')}. ${$t('footer.rights')}`"></p>
        <div class="flex flex-wrap items-center gap-4 text-xs text-muted">
            <a href="{{ url('/vender-meu-carro') }}" class="hover:text-brand-500" x-text="$t('footer.notice')"></a>
            <a href="#" class="hover:text-brand-500" x-text="$t('footer.help')"></a>
            <a href="#" class="hover:text-brand-500" x-text="$t('footer.privacy')"></a>
        </div>
    </div>
</footer>
