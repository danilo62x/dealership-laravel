<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Route;

// ─── Site público ────────────────────────────────────────────
Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/estoque', [PagesController::class, 'stock'])->name('stock');
Route::get('/veiculo/{slug}', [PagesController::class, 'vehicle'])->name('vehicle');
Route::get('/financiamento', [PagesController::class, 'finance'])->name('finance');
Route::get('/test-drive', [PagesController::class, 'testDrive'])->name('test-drive');
Route::get('/vender-meu-carro', [PagesController::class, 'sellCar'])->name('sell-car');

// ─── Autenticação (só visitante, layout em branco) ───────────
Route::middleware('guest.mock')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Área do showroom (sidebar + topbar, auth-gated mock) ────
Route::middleware('auth.mock')->group(function () {
    Route::get('/app', [PagesController::class, 'dashboard'])->name('dashboard');
    Route::get('/app/estoque', [PagesController::class, 'stockAdmin'])->name('app.stock');
    Route::get('/app/veiculo/{id}', [PagesController::class, 'vehicleAdmin'])->name('app.vehicle');
    Route::get('/app/propostas', [PagesController::class, 'deals'])->name('app.deals');
    Route::get('/app/proposta/{id}', [PagesController::class, 'deal'])->name('app.deal');
    Route::get('/app/test-drives', [PagesController::class, 'drives'])->name('app.drives');
    Route::get('/app/avaliacoes', [PagesController::class, 'appraisals'])->name('app.appraisals');
    Route::get('/app/clientes', [PagesController::class, 'customers'])->name('app.customers');
    Route::get('/app/vendedores', [PagesController::class, 'sellers'])->name('app.sellers');
    Route::get('/app/configuracoes', [PagesController::class, 'settings'])->name('app.settings');
});

// ─── Fallback 404 ────────────────────────────────────────────
Route::fallback(fn () => response()->view('pages.not-found', ['pageTitle' => '404'], 404));
