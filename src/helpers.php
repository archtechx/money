<?php

declare(strict_types=1);

use ArchTech\Money\Currency;
use ArchTech\Money\CurrencyManager;
use ArchTech\Money\Money;

if (! function_exists('money')) {
    /** Create a Money instance. */
    function money(int $amount, Currency|string $currency = null): Money
    {
        return new Money($amount, $currency ?? currencies()->getDefault());
    }
}

if (! function_exists('currency')) {
    /** Fetch a currency. If no argument is provided, the current currency will be returned. */
    function currency(Currency|string $currency = null): Currency
    {
        if ($currency) {
            return $currency instanceof Currency
                ? $currency
                : currencies()->get($currency);
        }

        return currencies()->getCurrent();
    }
}

if (! function_exists('currencies')) {
    /** Get the CurrencyManager instance. */
    function currencies(): CurrencyManager
    {
        return app(CurrencyManager::class);
    }
}
