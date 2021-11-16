<?php

declare(strict_types=1);

namespace ArchTech\Money;

use Illuminate\Support\ServiceProvider;

class MoneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrencyManager::class);
    }
}
