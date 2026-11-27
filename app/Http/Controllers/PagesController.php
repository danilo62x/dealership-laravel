<?php

namespace App\Http\Controllers;

use App\Support\DlData;
use Illuminate\View\View;

/**
 * As telas da Garagem — vitrine pública e área do showroom.
 *
 * Cada ação só escolhe a view e o título; os dados vêm de `DlData` e dos
 * stores do Alpine, que nascem da mesma semente.
 */
class PagesController extends Controller
{
    // ---- Site público ----------------------------------------------------

    public function home(): View
    {
        return view('pages.home', ['pageTitle' => null]);
    }

    public function stock(): View
    {
        return view('pages.stock', ['pageTitle' => 'Estoque']);
    }

    public function vehicle(string $slug): View
    {
        $vehicle = DlData::vehicleBySlug($slug);

        return view('pages.vehicle', [
            'pageTitle' => $vehicle ? DlData::vehicleLabel($vehicle) : 'Veículo não encontrado',
            'slug' => $slug,
        ]);
    }

    public function finance(): View
    {
        return view('pages.finance', ['pageTitle' => 'Financiamento']);
    }

    public function testDrive(): View
    {
        return view('pages.test-drive', ['pageTitle' => 'Test-drive']);
    }

    public function sellCar(): View
    {
        return view('pages.sell-car', ['pageTitle' => 'Vender meu carro']);
    }

    // ---- Área do showroom ------------------------------------------------

    public function dashboard(): View
    {
        return view('pages.app.dashboard', ['pageTitle' => 'Visão geral']);
    }

    public function stockAdmin(): View
    {
        return view('pages.app.stock', ['pageTitle' => 'Estoque']);
    }

    public function vehicleAdmin(string $id): View
    {
        $vehicle = DlData::vehicleById((int) $id);

        return view('pages.app.vehicle', [
            'pageTitle' => $vehicle ? DlData::vehicleLabel($vehicle) : 'Veículo não encontrado',
            'vehicleId' => (int) $id,
        ]);
    }

    public function deals(): View
    {
        return view('pages.app.deals', ['pageTitle' => 'Propostas']);
    }

    public function deal(string $id): View
    {
        return view('pages.app.deal', [
            'pageTitle' => 'Proposta #'.(int) $id,
            'dealId' => (int) $id,
        ]);
    }

    public function drives(): View
    {
        return view('pages.app.drives', ['pageTitle' => 'Test-drives']);
    }

    public function appraisals(): View
    {
        return view('pages.app.appraisals', ['pageTitle' => 'Avaliações']);
    }

    public function customers(): View
    {
        return view('pages.app.customers', ['pageTitle' => 'Clientes']);
    }

    public function sellers(): View
    {
        return view('pages.app.sellers', ['pageTitle' => 'Vendedores']);
    }

    public function settings(): View
    {
        return view('pages.app.settings', ['pageTitle' => 'Configurações']);
    }
}
