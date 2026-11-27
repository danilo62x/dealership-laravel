{{-- Propostas — porte de pages/app/DealsPage.tsx. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('deals.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <x-app.page-header title="$t('deals.title')" subtitle="$t('deals.subtitle')" :crumbs="$crumbs" />

    <x-dl.deal-board />
</x-layout.app>
