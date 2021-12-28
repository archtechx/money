<?php

declare(strict_types=1);

namespace ArchTech\Money;

use ArchTech\Money\Exceptions\MissingCurrencyManagerExtensionException;
use Illuminate\Support\ServiceProvider;

class MoneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrencyManager::class);
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'money');

        $class = $this->app->make(config('money.manager'));

        /**
         * Make sure that the CurrencyManager is loaded as expected
         */
        if (!is_a($class, CurrencyManager::class)) {
            throw new MissingCurrencyManagerExtensionException($class::class);
        }

        $this->app->singleton($class::class);

    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('money.php'),
        ], 'money-config');
    }
}
