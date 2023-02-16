<?php

declare(strict_types=1);

namespace ArchTech\Money;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Livewire\Wireable;

/** @implements Arrayable<string, string|int> */
final class Money implements JsonSerializable, Arrayable, Wireable
{
    protected int $value;
    protected Currency $currency;

    /** Create a new Money instance. */
    public function __construct(int $value, Currency|string $currency = null)
    {
        $this->value = $value;
        $this->currency = currency($currency);
    }

    /** Create a new Money instance with the same currency. */
    public function new(int $value): self
    {
        return new self($value, $this->currency);
    }

    /** Create a new Money instance with the same currency from a decimal value. */
    protected function newFromDecimal(float $decimal): self
    {
        return static::fromDecimal($decimal, $this->currency);
    }

    /** Create a Money instance from a decimal value. */
    public static function fromDecimal(float $decimal, Currency|string $currency = null): self
    {
        return new static(
            (int) round($decimal * pow(10, currency($currency)->mathDecimals())),
            currency($currency)
        );
    }

    /** Add money (in base value). */
    public function add(int $value): self
    {
        return $this->new($this->value + $value);
    }

    /** Add money (from another Money instance). */
    public function addMoney(self $money): self
    {
        return $this->add(
            $money->convertTo($this->currency)->value()
        );
    }

    /** Add money (in decimal value). */
    public function addDecimal(float $decimal): self
    {
        return $this->addMoney(
            $this->newFromDecimal($decimal)
        );
    }

    /** Subtract money (in base value). */
    public function subtract(int $value): self
    {
        return $this->new($this->value - $value);
    }

    /** Subtract money (in decimal value). */
    public function subtractDecimal(float $decimal): self
    {
        return $this->subtractMoney(
            $this->newFromDecimal($decimal)
        );
    }

    /** Subtract money (of another Money instance). */
    public function subtractMoney(self $money): self
    {
        return $this->subtract(
            $money->convertTo($this->currency)->value()
        );
    }

    /** Multiply the money by a coefficient. */
    public function multiplyBy(float $coefficient): self
    {
        return $this->new(
            (int) round($this->value * $coefficient)
        );
    }

    /** Multiply the money by a coefficient. */
    public function times(float $coefficient): self
    {
        return $this->multiplyBy($coefficient);
    }

    /** Divide the money by a number. */
    public function divideBy(float $number): self
    {
        if ($number == 0) {
            $number = 1;
        }

        return $this->new(
            (int) round($this->value() / $number)
        );
    }

    /** Add a % fee to the money. */
    public function addFee(float $rate): self
    {
        return $this->multiplyBy(
            round(1 + ($rate / 100), $this->currency->mathDecimals())
        );
    }

    /** Add a % tax to the money. */
    public function addTax(float $rate): self
    {
        return $this->addFee($rate);
    }

    /** Subtract a % fee from the money. */
    public function subtractFee(float $rate): self
    {
        return $this->divideBy(
            round(1 + ($rate / 100), $this->currency->mathDecimals())
        );
    }

    /** Subtract a % tax from the money. */
    public function subtractTax(float $rate): self
    {
        return $this->subtractFee($rate);
    }

    /** Get the base value of the money in the used currency. */
    public function value(): int
    {
        return $this->value;
    }

    /** Get the used currency. */
    public function currency(): Currency
    {
        return $this->currency;
    }

    /** Get the decimal representation of the value. */
    public function decimal(): float
    {
        return $this->value / pow(10, $this->currency->mathDecimals());
    }

    /** Format the value. */
    public function formatted(mixed ...$overrides): string
    {
        return PriceFormatter::format($this->decimal(), $this->currency, variadic_array($overrides));
    }

    /** Format the raw (unrounded) value. */
    public function rawFormatted(mixed ...$overrides): string
    {
        return $this->formatted(array_merge(variadic_array($overrides), [
            'displayDecimals' => $this->currency->mathDecimals(),
        ]));
    }

    /**
     * Create a Money instance from a formatted string.
     *
     * @param  string  $formatted The string formatted using the `formatted()` or `rawFormatted()` method.
     * @param  Currency|string|null  $currency The currency to use when passing the overrides. If not provided, the currency of the formatted string is used.
     * @param  array  ...$overrides The overrides used when formatting the money instance.
     */
    public static function fromFormatted(string $formatted, Currency|string $currency = null, mixed ...$overrides): self
    {
        $currency = isset($currency)
            ? currency($currency)
            : PriceFormatter::extractCurrency($formatted);

        $decimal = PriceFormatter::resolve($formatted, $currency, variadic_array($overrides));

        return static::fromDecimal($decimal, currency($currency));
    }

    /** Get the string representation of the Money instance. */
    public function __toString(): string
    {
        return $this->formatted();
    }

    /** Convert the instance to an array representation. */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'currency' => $this->currency->code(),
        ];
    }

    /** Check if the value equals the value of another Money instance, adjusted for currency. */
    public function equals(self $money): bool
    {
        return $this->valueInDefaultCurrency() === $money->valueInDefaultCurrency();
    }

    /** Check if the value and currency match another Money instance. */
    public function is(self $money): bool
    {
        return $this->currency()->code() === $money->currency()->code()
            && $this->equals($money);
    }

    /** Get the data used for JSON serializing this object. */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** Convert the instance to JSON */
    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /** Instantiate Money from JSON. */
    public static function fromJson(string|array $json): self
    {
        if (is_string($json)) {
            $json = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        }

        return new static($json['value'], $json['currency']);
    }

    /** Value in the default currency. */
    public function valueInDefaultCurrency(): int
    {
        $mathDecimalDifference = $this->currency->mathDecimals() - currencies()->getDefault()->mathDecimals();

        return $this
            ->divideBy($this->currency->rate())
            ->divideBy(pow(10, $mathDecimalDifference))
            ->value();
    }

    /** Convert the money to a different currency. */
    public function convertTo(Currency|string $currency): self
    {
        // We're converting from the current currency to the default currency, and then to the intended currency
        $newCurrency = currency($currency);
        $mathDecimalDifference = $newCurrency->mathDecimals() - currencies()->getDefault()->mathDecimals();

        return new static(
            (int) round($this->valueInDefaultCurrency() * $newCurrency->rate() * pow(10, $mathDecimalDifference), 0),
            $currency
        );
    }

    /** Convert the Money to the current currency. */
    public function toCurrent(): self
    {
        return $this->convertTo(currencies()->getCurrent());
    }

    /** Convert the Money to the current currency. */
    public function toDefault(): self
    {
        return $this->convertTo(currencies()->getDefault());
    }

    /** Round the Money to a custom precision. */
    public function rounded(int $precision = null): self
    {
        $precision ??= $this->currency->rounding();

        return $this->new(((int) round($this->value, -$precision)));
    }

    /** Get the money rounding (typically this is the difference between the actual value and the formatted value.) */
    public function rounding(): int
    {
        return $this->rounded()->value() - $this->value();
    }

    /** Get the cents from the decimal value. */
    public function cents(): self
    {
        return $this->newFromDecimal(
            $this->decimal() - floor($this->decimal())
        );
    }

    public function toLivewire()
    {
        return $this->toArray();
    }

    public static function fromLivewire($value)
    {
        return static::fromJson($value);
    }
}
