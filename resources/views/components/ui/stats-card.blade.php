@props([
    'title' => "''",
    'value' => "''",
    'icon' => 'chart',
    'accent' => "'brand'",
])
{{-- $title, $value e $accent são expressões Alpine.
     Porte de components/ui/StatsCard.tsx. --}}
<div class="surface-card rounded-2xl border border-themed p-5 shadow-theme-sm md:p-6"
    x-data="{ get accent() { return {{ $accent }} } }">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl"
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
            <x-icon :name="$icon" :size="22" />
        </span>
    </div>
    <div class="mt-5 flex items-end justify-between gap-3">
        <div class="min-w-0">
            <span class="text-sm text-muted" x-text="{{ $title }}"></span>
            <h4 class="mt-2 truncate text-title-sm font-bold text-heading" x-text="{{ $value }}"></h4>
        </div>
    </div>
</div>
