@props([
    'open' => 'false',
    'close' => '',
    'title' => "''",
    'subtitle' => null,
    'size' => 'md',
])
{{-- $open é uma expressão Alpine; $close é o corpo do @click de fechar.
     Porte de components/ui/ModalBase.tsx. --}}
@php
    $sizeMap = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'];
    $width = $sizeMap[$size] ?? $sizeMap['md'];
@endphp
<div x-show="{{ $open }}" x-cloak @keydown.escape.window="{{ $close }}"
    class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
    @click.self="{{ $close }}">
    <div class="surface-card relative max-h-[90vh] w-full overflow-y-auto rounded-2xl border shadow-theme-xl {{ $width }}">
        <header class="flex items-center justify-between gap-3 border-b border-themed px-5 py-4">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-heading" x-text="{{ $title }}"></h3>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-muted" x-text="{{ $subtitle }}"></p>
                @endif
            </div>
            <button type="button" aria-label="Fechar" @click="{{ $close }}"
                class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <x-icon name="x" :size="20" />
            </button>
        </header>
        <div class="px-5 py-5">{{ $slot }}</div>
        @isset($footer)
            <footer class="flex items-center justify-end gap-2 border-t border-themed px-5 py-3">{{ $footer }}</footer>
        @endisset
    </div>
</div>
