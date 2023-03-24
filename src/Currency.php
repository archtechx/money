<?php

declare(strict_types=1);

namespace ArchTech\Money;

use ArchTech\Money\Exceptions\InvalidCurrencyException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/** @implements Arrayable<string, string|int|float> */
class Currency implements Arrayable, JsonSerializable
{
    /** Code of the currency (e.g. 'CZK'). */
    protected string $code;

    /** Name of the currency (e.g. 'Czech Crown'). */
    protected string $name;

    /** Rate of this currency relative to the default currency. */
    protected float $rate;

    /** Prefix placed at the beginning of the formatted value. */
    protected string $prefix;

    /** Suffix placed at the end of the formatted value. */
    protected string $suffix;

    /** Number of decimals used in money calculations. */
    protected int $mathDecimals;

    /** Number of decimals used in the formatted value. */
    protected int $displayDecimals;

    /** The character used to separate the decimal values. */
    protected string $decimalSeparator;

    /** The character used to separate groups of thousands. */
    protected string $thousandsSeparator;

    /** How many decimals of the currency's values should get rounded. */
    protected int $rounding;

    /** Should trailing decimal zeros be trimmed. */
    protected bool $trimTrailingDecimalZeros;

    /** Create a new Currency instance. */
    public function __construct(
        string $code = null,
        string $name = null,
        float $rate = null,
        string $prefix = null,
        string $suffix = null,
        int $mathDecimals = null,
        int $displayDecimals = null,
        int $rounding = null,
        string $decimalSeparator = null,
        string $thousandsSeparator = null,
        bool $trimTrailingDecimalZeros = null,
    ) {
        $this->code = $code ?? $this->code ?? '';
        $this->name = $name ?? $this->name ?? '';
        $this->rate = $rate ?? $this->rate ?? 1.0;
        $this->prefix = $prefix ?? $this->prefix ?? '';
        $this->suffix = $suffix ?? $this->suffix ?? '';
        $this->mathDecimals = $mathDecimals ?? $this->mathDecimals ?? 2;
        $this->displayDecimals = $displayDecimals ?? $this->displayDecimals ?? 2;
        $this->decimalSeparator = $decimalSeparator ?? $this->decimalSeparator ?? '.';
        $this->thousandsSeparator = $thousandsSeparator ?? $this->thousandsSeparator ?? ',';
        $this->rounding = $rounding ?? $this->rounding ?? $this->mathDecimals;
        $this->trimTrailingDecimalZeros = $trimTrailingDecimalZeros ?? $this->trimTrailingDecimalZeros ?? false;

        $this->check();
    }

    /** Create an anonymous Currency instance from an array. */
    public static function fromArray(array $currency): static
    {
        return new static(...$currency);
    }

    /** Get the currency's code. */
    public function code(): string
    {
        return $this->code;
    }

    /** Get the currency's name. */
    public function name(): string
    {
        return $this->name;
    }

    /** Get the currency's rate. */
    public function rate(): float
    {
        return $this->rate;
    }

    /** Get the currency's prefix. */
    public function prefix(): string
    {
        return $this->prefix;
    }

    /** Get the currency's suffix. */
    public function suffix(): string
    {
        return $this->suffix;
    }

    /** Get the currency's math decimal count. */
    public function mathDecimals(): int
    {
        return $this->mathDecimals;
    }

    /** Get the currency's math decimal count. */
    public function displayDecimals(): int
    {
        return $this->displayDecimals;
    }

    /** Get the currency's decimal separator. */
    public function decimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    /** Get the currency's thousands separator. */
    public function thousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }

    /** Get the currency's rounding. */
    public function rounding(): int
    {
        return $this->rounding;
    }

    /** Get the currency's setting for trimming trailing decimal zeros. */
    public function trimTrailingDecimalZeros(): bool
    {
        return $this->trimTrailingDecimalZeros;
    }

    /** Convert the currency to a string (returns the code). */
    public function __toString()
    {
        return $this->code();
    }

    /** Convert the currency to an array. */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'rate' => $this->rate,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'mathDecimals' => $this->mathDecimals,
            'displayDecimals' => $this->displayDecimals,
            'rounding' => $this->rounding,
            'decimalSeparator' => $this->decimalSeparator,
            'thousandsSeparator' => $this->thousandsSeparator,
            'trimTrailingDecimalZeros' => $this->trimTrailingDecimalZeros,
        ];
    }

    /** Get the data used for JSON serialization. */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** Create a currency from JSON. */
    public static function fromJson(string|array $json): self
    {
        if (is_string($json)) {
            $json = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        }

        return static::fromArray($json);
    }

    /**
     * Ensure that the currency has all required values.
     *
     * @throws InvalidCurrencyException
     */
    protected function check(): void
    {
        if (! $this->code() || ! $this->name()) {
            throw new InvalidCurrencyException('This currency does not have a code or a name.');
        }
    }
}
