<?php

declare(strict_types=1);

namespace ArchTech\Money;

class PriceFormatter
{
    /** Format a decimal per the currency's specifications. */
    public static function format(float $decimal, Currency $currency, array $overrides = []): string
    {
        $currency = Currency::fromArray(
            array_merge(currency($currency)->toArray(), $overrides)
        );

        $decimal = number_format(
            $decimal,
            $currency->displayDecimals(),
            $currency->decimalSeparator(),
            $currency->thousandsSeparator(),
        );

        return $currency->prefix() . $decimal . $currency->suffix();
    }
}
