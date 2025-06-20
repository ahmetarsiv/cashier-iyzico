<?php

namespace Laravel\Cashier;

use Illuminate\Support\ServiceProvider;

class CashierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/cashier-iyzico.php', 'cashier-iyzico');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/config/cashier-iyzico.php' => config_path('cashier-iyzico.php'),
        ], 'cashier-iyzico-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations'),
        ], 'cashier-iyzico-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
