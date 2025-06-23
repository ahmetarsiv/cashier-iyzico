<?php

namespace Codenteq\Iyzico;

use Illuminate\Support\ServiceProvider;

/**
 * Laravel Cashier İyzico Service Provider
 *
 * This service provider registers all the necessary services, configurations,
 * and components required for the Laravel Cashier İyzico package.
 *
 * @package Codenteq\LaravelCashierIyzico
 */
class CashierServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cashier.php', 'cashier');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cashier');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/cashier.php' => config_path('cashier.php'),
            ], 'cashier-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'cashier-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/cashier'),
            ], 'cashier-views');
        }
    }
}
