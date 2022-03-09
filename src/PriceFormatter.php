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

    /** Extract the decimal from the formatter string as per the currency's specifications. */
    public static function resolve(string $formatted, Currency $currency, array $overrides = []): float
    {
        $currency = Currency::fromArray(
            array_merge(currency($currency)->toArray(), $overrides)
        );

        $formatted = ltrim($formatted, $currency->prefix());
        $formatted = rtrim($formatted, $currency->suffix());

        $removeNonDigits = preg_replace('/[^\d' . preg_quote($currency->decimalSeparator()) . ']/', '', $formatted);

        return (float) str_replace($currency->decimalSeparator(), '.', $removeNonDigits);
    }
}
