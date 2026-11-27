@props([
    'page' => 'page',
    'totalPages' => 'totalPages',
    'change' => 'page = $event',
])
{{-- $page e $totalPages são expressões Alpine; $change recebe a página nova em
     `$event`. Porte de components/ui/Pagination.tsx. --}}
<nav aria-label="Pagination" x-show="({{ $totalPages }}) > 1" x-cloak
    x-data="{
        get current() { return {{ $page }} },
        get total() { return {{ $totalPages }} },
        get pages() {
            const out = [];
            for (let i = 1; i <= this.total; i++) {
                if (i === 1 || i === this.total || (i >= this.current - 1 && i <= this.current + 1)) out.push(i);
                else if (out[out.length - 1] !== '...') out.push('...');
            }
            return out;
        },
        go($event) { {{ $change }} },
    }"
    class="flex flex-wrap items-center justify-center gap-2">
    <button type="button" aria-label="Previous" :disabled="current <= 1" @click="go(current - 1)"
        class="flex h-10 min-w-10 items-center justify-center rounded-lg border border-themed px-3 text-sm font-medium text-muted transition hover:text-heading disabled:opacity-40">
        <x-icon name="chevron-left" :size="16" />
    </button>
    <template x-for="(p, i) in pages" :key="i">
        <span>
            <template x-if="p === '...'">
                <span class="px-1 text-muted">…</span>
            </template>
            <template x-if="p !== '...'">
                <button type="button" @click="go(p)" x-text="p"
                    class="flex h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                    :class="p === current ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body hover:text-heading'"></button>
            </template>
        </span>
    </template>
    <button type="button" aria-label="Next" :disabled="current >= total" @click="go(current + 1)"
        class="flex h-10 min-w-10 items-center justify-center rounded-lg border border-themed px-3 text-sm font-medium text-muted transition hover:text-heading disabled:opacity-40">
        <x-icon name="chevron-right" :size="16" />
    </button>
</nav>
