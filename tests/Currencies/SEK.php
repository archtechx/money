<?php

namespace ArchTech\Money\Tests\Currencies;

use ArchTech\Money\Currency;

class SEK extends Currency
{
    protected string $code = 'SEK';
    protected string $name = 'Swedish crown';
    protected float $rate = 9.94;
    protected int $mathDecimals = 4;
    protected int $displayDecimals = 2;
    protected int $rounding = 0;
    protected string $suffix = ' kr';
    protected bool $trimTrailingDecimalZeros = true;
}
