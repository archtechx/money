<?php

declare(strict_types=1);

namespace ArchTech\Money;

use ArchTech\Money\Concerns\PersistsCurrency;
use ArchTech\Money\Concerns\RegistersCurrencies;
use ArchTech\Money\Currencies\USD;

class CurrencyManager
{
    use RegistersCurrencies, PersistsCurrency;

    /** The default currency's code. */
    protected string $default = 'USD';

    /** The current currency's code. */
    protected string $current;

    public function __construct()
    {
        $this->reset();
    }

    /** Reset the object to the default state. */
    public function reset(): static
    {
        $this->currencies = [
            'USD' => new USD,
        ];

        $this->default = 'USD';

        $this->forgetCurrent();

        return $this;
    }

    public function forgetCurrent(): static
    {
        unset($this->current);

        return $this;
    }

    /** Get the default currency. */
    public function getDefault(): Currency
    {
        return $this->get($this->default);
    }

    /** Set the default currency. */
    public function setDefault(string $currency): static
    {
        $code = $this->getCode($currency);

        $this->ensureCurrencyExists($code);

        $this->default = $code;

        return $this;
    }

    /** Get the current currency. */
    public function getCurrent(): Currency
    {
        return $this->get($this->current ??= $this->resolveCurrent() ?? $this->default);
    }

    /** Set the current currency. */
    public function setCurrent(Currency|string $currency): static
    {
        $code = $this->getCode($currency);

        $this->ensureCurrencyExists($code);

        $this->storeCurrent($this->current = $code);

        return $this;
    }
}
