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
        $this->publishes([
            __DIR__.'/config/cashier-iyzico.php' => config_path('cashier-iyzico.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}
