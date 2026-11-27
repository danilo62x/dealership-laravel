{{-- Test-drive — porte de pages/public/TestDrivePage.tsx.
     A página do veículo manda o carro escolhido em ?veiculo=<id>. --}}
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-6">
            <h1 class="text-title-sm font-bold text-heading" x-text="$t('drive.title')"></h1>
            <p class="mt-1 text-sm text-muted" x-text="$t('drive.subtitle')"></p>
        </header>

        <x-dl.drive-scheduler />
    </div>
</x-layout.public>
