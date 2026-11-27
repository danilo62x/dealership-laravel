@props([
    'icon' => 'package',
    'iconExpr' => null,
    'iconNames' => [],
    'title' => "''",
    'description' => null,
])
{{-- $title e $description são expressões Alpine. Porte de ui/EmptyState.tsx. --}}
<div class="flex flex-col items-center justify-center py-16 text-center">
    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/15">
        @if ($iconExpr)
            <x-ui.icon-dyn :expr="$iconExpr" :names="$iconNames" :size="28" />
        @else
            <x-icon :name="$icon" :size="28" />
        @endif
    </span>
    <h3 class="mt-5 text-lg font-semibold text-heading" x-text="{{ $title }}"></h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-muted" x-text="{{ $description }}"></p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
