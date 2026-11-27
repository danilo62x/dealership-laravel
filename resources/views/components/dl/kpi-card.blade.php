@props([
    'title' => "''",
    'value' => "''",
    'hint' => null,
    'icon' => 'chart',
    'accent' => "'brand'",
])
{{-- Cartão de indicador — porte de components/dl/KpiCard.tsx.
     $title, $value, $hint e $accent são expressões Alpine. --}}
<div class="surface-card rounded-2xl border p-5 shadow-theme-sm" x-data="{ get accent() { return {{ $accent }} } }">
    <div class="flex items-start justify-between gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
            :class="({
                brand: 'bg-brand-50 dark:bg-brand-500/15',
                success: 'bg-success-50 dark:bg-success-500/15',
                warning: 'bg-warning-50 dark:bg-warning-500/15',
                error: 'bg-error-50 dark:bg-error-500/15',
                info: 'bg-blue-light-50 dark:bg-blue-light-500/15',
            })[accent]">
            <span :class="({
                    brand: 'text-brand-500',
                    success: 'text-success-600',
                    warning: 'text-warning-600',
                    error: 'text-error-500',
                    info: 'text-blue-light-500',
                })[accent]">
                <x-icon :name="$icon" :size="20" />
            </span>
        </span>
    </div>
    <p class="mt-4 text-sm text-muted" x-text="{{ $title }}"></p>
    <p class="mt-1 text-2xl font-bold text-heading" x-text="{{ $value }}"></p>
    @if ($hint)
        <p class="mt-1 text-xs text-muted" x-text="{{ $hint }}"></p>
    @endif
</div>
