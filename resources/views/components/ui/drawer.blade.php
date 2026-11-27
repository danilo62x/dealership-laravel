@props([
    'open' => 'false',
    'close' => '',
    'title' => "''",
    'subtitle' => null,
    'width' => 'max-w-md',
])
{{-- Gaveta lateral — porte de components/ui/Drawer.tsx.
     $open é uma expressão Alpine; $close é o corpo do @click de fechar. --}}
<div x-show="{{ $open }}" x-cloak @keydown.escape.window="{{ $close }}" class="fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-gray-900/50" @click="{{ $close }}"></div>
    <aside role="dialog"
        class="surface-card absolute top-0 right-0 flex h-full w-full {{ $width }} flex-col border-l shadow-theme-xl">
        <div class="flex items-start justify-between border-b border-themed px-5 py-4">
            <div class="min-w-0">
                <h2 class="text-lg font-semibold text-heading" x-text="{{ $title }}"></h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-muted" x-text="{{ $subtitle }}"></p>
                @endif
            </div>
            <button type="button" aria-label="Fechar" @click="{{ $close }}"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-muted hover:text-heading">
                <x-icon name="x" :size="20" />
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4">{{ $slot }}</div>
        @isset($footer)
            <div class="border-t border-themed px-5 py-4">{{ $footer }}</div>
        @endisset
    </aside>
</div>
