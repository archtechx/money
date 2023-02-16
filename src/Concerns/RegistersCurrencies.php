<?php

declare(strict_types=1);

namespace ArchTech\Money\Concerns;

use ArchTech\Money\Currency;
use ArchTech\Money\Exceptions\CurrencyDoesNotExistException;
use ArchTech\Money\Exceptions\InvalidCurrencyException;
use Closure;
use ReflectionClass;

trait RegistersCurrencies
{
    /**
     * Registered currencies.
     *
     * @var array<string, Currency>
     */
    protected array $currencies = [];

    /** Register a currency. */
    public function add(string|Currency|Closure|array $currencies): static
    {
        // $currencies can be:
        // new Currency(...)
        // [new Currency(..), new Currency(...)]
        // USD::class
        // new USD
        // ['code' => 'GBP', 'rate' => 0.8, 'name' => 'British Pound']
        // Or a Closure returning any of the above

        // Invoke Closures
        $currencies = value($currencies);

        // Make sure we're working with an array
        $currencies = is_array($currencies) ? $currencies : [$currencies];

        // If we're working with a single currency as an
        // array, we'll manually wrap it again in [].
        if (isset($currencies['code'])) {
            $currencies = [$currencies];
        }

        foreach ($currencies as $currency) {
            // ['code' => 'GBP', 'rate' => 0.8, 'name' => 'British Pound']
            if (is_array($currency)) {
                $currency = Currency::fromArray($currency);
            }

            // USD::class
            if (is_string($currency)) {
                $currency = new $currency;
            }

            /** @var Currency $currency */
            $this->currencies[$currency->code()] = $currency;
        }

        return $this;
    }

    /** Unregister a currency. */
    public function remove(string $currency): static
    {
        $code = $this->getCode($currency);

        if ($this->has($code)) {
            unset($this->currencies[$code]);
        }

        return $this;
    }

    /** List all registered currencies */
    public function all(): array
    {
        return $this->currencies;
    }

    /** Unregister all currencies. */
    public function clear(): static
    {
        $this->currencies = [];

        return $this;
    }

    /** Fetch a currency by its code. */
    public function get(string $currency): Currency
    {
        // Converting this to the code in case a class string is passed
        $code = $this->getCode($currency);

        $this->ensureCurrencyExists($code);

        return $this->currencies[$code];
    }

    /** Check if a currency is registered. */
    public function has(string $currency): bool
    {
        // Converting this to the code in case a class string is passed
        $code = $this->getCode($currency);

        return isset($this->currencies[$code]);
    }

    /** Abort execution if a currency doesn't exist. */
    public function ensureCurrencyExists(string $currency): static
    {
        if (! $this->has($currency)) {
            throw new CurrencyDoesNotExistException($currency);
        }

        return $this;
    }

    /** Get a currency's code. */
    public function getCode(Currency|string $currency): string
    {
        if (is_string($currency) && isset($this->currencies[$currency])) {
            return $currency;
        }

        if ($currency instanceof Currency) {
            return $currency->code();
        }

        if (class_exists($currency) && (new ReflectionClass($currency))->isSubclassOf(Currency::class)) {
            /** @var Currency $currency * */
            $currency = new $currency;

            return $currency->code();
        }

        throw new InvalidCurrencyException(
            "{$currency} is not a valid currency.",
        );
    }
}
