<?php

namespace App\Support;

/**
 * Menus do template em um lugar só — a barra lateral do showroom e os links do
 * site público. Porte das constantes `SECTIONS` de `AppSidebar.tsx` e `LINKS`
 * de `PublicNavbar.tsx`.
 *
 * Registrado como singleton e compartilhado com todas as views como `$menu`
 * (ver `AppServiceProvider`).
 *
 * `badge` diz qual contador do Alpine a entrada mostra: `stock` traz o número
 * de veículos em giro lento, derivado dos dias em estoque — nunca um número
 * fixo. `available` é o total da vitrine pública.
 */
class MenuRegistry
{
    /** Caminho da requisição atual, sempre com a barra na frente. */
    private function path(): string
    {
        return '/'.ltrim(request()->path(), '/');
    }

    /** @return array<int,array<string,mixed>> */
    public function publicLinks(): array
    {
        $path = $this->path();

        return [
            ['href' => url('/'), 'key' => 'nav.home', 'active' => $path === '/'],
            ['href' => url('/estoque'), 'key' => 'nav.stock', 'active' => str_starts_with($path, '/estoque') || str_starts_with($path, '/veiculo'), 'badge' => 'available'],
            ['href' => url('/financiamento'), 'key' => 'nav.finance', 'active' => $path === '/financiamento'],
            ['href' => url('/test-drive'), 'key' => 'nav.testDrive', 'active' => $path === '/test-drive'],
            ['href' => url('/vender-meu-carro'), 'key' => 'nav.sellCar', 'active' => $path === '/vender-meu-carro'],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function appSections(): array
    {
        $path = $this->path();

        return [
            [
                'titleKey' => 'appNav.salesSection',
                'items' => [
                    ['to' => url('/app'), 'labelKey' => 'appNav.dashboard', 'icon' => 'dashboard', 'active' => $path === '/app'],
                    ['to' => url('/app/propostas'), 'labelKey' => 'appNav.deals', 'icon' => 'handshake', 'active' => str_starts_with($path, '/app/propost')],
                    ['to' => url('/app/test-drives'), 'labelKey' => 'appNav.drives', 'icon' => 'steering', 'active' => $path === '/app/test-drives'],
                ],
            ],
            [
                'titleKey' => 'appNav.stockSection',
                'items' => [
                    ['to' => url('/app/estoque'), 'labelKey' => 'appNav.stock', 'icon' => 'car', 'active' => str_starts_with($path, '/app/estoque') || str_starts_with($path, '/app/veiculo'), 'badge' => 'slow'],
                    ['to' => url('/app/avaliacoes'), 'labelKey' => 'appNav.appraisals', 'icon' => 'calculator', 'active' => $path === '/app/avaliacoes'],
                ],
            ],
            [
                'titleKey' => 'appNav.peopleSection',
                'items' => [
                    ['to' => url('/app/clientes'), 'labelKey' => 'appNav.customers', 'icon' => 'users', 'active' => $path === '/app/clientes'],
                    ['to' => url('/app/vendedores'), 'labelKey' => 'appNav.sellers', 'icon' => 'user-check', 'active' => $path === '/app/vendedores'],
                ],
            ],
            [
                'titleKey' => 'appNav.accountSection',
                'items' => [
                    ['to' => url('/app/configuracoes'), 'labelKey' => 'appNav.settings', 'icon' => 'settings', 'active' => $path === '/app/configuracoes'],
                ],
            ],
        ];
    }
}
