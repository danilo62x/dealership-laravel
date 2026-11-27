<?php

namespace App\Providers;

use App\Support\MenuRegistry;
use App\Support\MockUsers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuRegistry::class, fn () => new MenuRegistry());
        $this->app->singleton(MockUsers::class, fn () => new MockUsers());
    }

    public function boot(): void
    {
        // Liga <x-icon> ao componente anônimo de SVG embutido.
        Blade::component('components.icon', 'icon');

        // Menu e usuário (fictício) disponíveis em todas as views.
        View::composer('*', function ($view) {
            $view->with('menu', app(MenuRegistry::class));
            $view->with('currentUser', session('user'));
        });
    }
}
