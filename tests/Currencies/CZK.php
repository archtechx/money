<?php

namespace ArchTech\Money\Tests\Currencies;

use ArchTech\Money\Currency;

class CZK extends Currency
{
    protected string $code = 'CZK';
    protected string $name = 'Czech Crown';
    protected float $rate = 25;
    protected int $mathDecimals = 2;
    protected int $displayDecimals = 0;
    protected string $decimalSeparator = ',';
    protected string $thousandsSeparator = '.';
    protected int $rounding = 2;
    protected string $suffix = ' Kč';
    protected bool $trimTrailingDecimalZeros = false;
}
