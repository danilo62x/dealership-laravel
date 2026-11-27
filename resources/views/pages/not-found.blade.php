{{-- 404 — porte de pages/app/NotFoundPage.tsx. --}}
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="flex min-h-[70vh] w-full flex-col items-center justify-center px-4 py-16 text-center"
        style="background-color: var(--tx-body-bg)">
        <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-50 text-brand-500 dark:bg-brand-500/15">
            <x-icon name="car" :size="36" />
        </span>
        <p class="mt-6 text-title-md font-bold text-heading">404</p>
        <h1 class="mt-1 text-xl font-semibold text-heading" x-text="$t('notFound.title')"></h1>
        <p class="mt-3 max-w-md text-sm text-muted" x-text="$t('notFound.text')"></p>
        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/') }}"
                class="inline-flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                <x-icon name="home" :size="16" />
                <span x-text="$t('notFound.home')"></span>
            </a>
            <a href="{{ url('/estoque') }}"
                class="inline-flex h-11 items-center gap-2 rounded-lg border border-themed px-5 text-sm font-medium text-body transition hover:bg-gray-50 dark:hover:bg-gray-800"
                x-text="$t('notFound.stock')"></a>
        </div>
    </div>
</x-layout.public>
