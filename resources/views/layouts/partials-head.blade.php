{{-- <head> compartilhado + bootstrap de window.DL para todos os layouts. --}}
<meta charset="UTF-8" />
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="manifest" href="/manifest.webmanifest" />
<meta name="theme-color" content="#0aa295" />
<title>{{ isset($pageTitle) && $pageTitle ? $pageTitle . ' · ' : '' }}Garagem — Concessionária</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    // Dados canônicos compartilhados com os stores do Alpine
    // (espelha dealership/react src/data + src/stores). A semente nasce do
    // mesmo mulberry32(20260905) do React, então o estoque é o mesmo.
    window.DL = {
        brands: @json(\App\Support\DlData::brands()),
        models: @json(\App\Support\DlData::models()),
        vehicles: @json(\App\Support\DlData::vehicles()),
        customers: @json(\App\Support\DlData::customers()),
        sellers: @json(\App\Support\DlData::sellers()),
        deals: @json(\App\Support\DlData::deals()),
        tradeIns: @json(\App\Support\DlData::tradeIns()),
        drives: @json(\App\Support\DlData::drives()),
        rules: @json(\App\Support\DlData::rules()),
        optionCatalog: @json(\App\Support\DlData::OPTION_CATALOG),
        shop: @json(\App\Support\DlData::store()),
        slots: @json(\App\Support\DlData::SLOTS),
        // O "hoje" da semente: meia-noite local, para os dias em estoque não
        // mudarem entre uma recarga e outra.
        anchor: {{ \App\Support\DlData::anchor() }},
        todayKey: @json(\App\Support\DlData::todayKey()),
        seededAt: {{ \App\Support\DlData::nowMs() }},
        seedTtlMs: {{ \App\Support\DlData::SEED_TTL_MS }},
        visitorCustomerId: {{ \App\Support\DlData::VISITOR_CUSTOMER_ID }},
        user: @json(session('user')),
        demo: { email: @json(\App\Support\MockUsers::DEMO_EMAIL), password: @json(\App\Support\MockUsers::DEMO_PASSWORD) },
    };
</script>
@stack('head')
