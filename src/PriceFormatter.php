<?php

declare(strict_types=1);

namespace ArchTech\Money;

use ArchTech\Money\Exceptions\CannotExtractCurrencyException;
use Exception;

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

        if ($currency->trimTrailingDecimalZeros()) {
            // Remove trailing zeros from the formatted string
            $decimal = rtrim($decimal, '0');

            // Once there are no more decimal values, remove the decimal separator as well
            $decimal = rtrim($decimal, $currency->decimalSeparator());
        }

        return $currency->prefix() . $decimal . $currency->suffix();
    }

    /** Extract the decimal from the formatted string as per the currency's specifications. */
    public static function resolve(string $formatted, Currency $currency, array $overrides = []): float
    {
        $currency = Currency::fromArray(
            array_merge(currency($currency)->toArray(), $overrides)
        );

        $formatted = ltrim($formatted, $currency->prefix());
        $formatted = rtrim($formatted, $currency->suffix());

        $removeNonDigits = preg_replace('/[^\d' . preg_quote($currency->decimalSeparator()) . ']/', '', $formatted);

        if (! is_string($removeNonDigits)) {
            throw new Exception('The formatted string could not be resolved to a valid number.');
        }

        return (float) str_replace($currency->decimalSeparator(), '.', $removeNonDigits);
    }

    /** Tries to extract the currency from the formatted string. */
    public static function extractCurrency(string $formatted): Currency
    {
        $possibleCurrency = null;

        foreach (currencies()->all() as $currency) {
            if (
                str_starts_with($formatted, $currency->prefix())
                && str_ends_with($formatted, $currency->suffix())
            ) {
                if ($possibleCurrency) {
                    throw new CannotExtractCurrencyException("Multiple currencies are using the same prefix and suffix as '$formatted'. Please specify the currency of the formatted string.");
                }

                $possibleCurrency = $currency;
            }
        }

        return $possibleCurrency ?? throw new CannotExtractCurrencyException("None of the currencies are using the prefix and suffix that would match with the formatted string '$formatted'.");
    }
}
