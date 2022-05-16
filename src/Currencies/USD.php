<?php

declare(strict_types=1);

namespace ArchTech\Money\Currencies;

use ArchTech\Money\Currency;

class USD extends Currency
{
    protected string $code = 'USD';
    protected string $name = 'United States Dollar';
    protected float $rate = 1.0;
    protected int $mathDecimals = 2;
    protected int $displayDecimals = 2;
    protected int $rounding = 2;
    protected string $prefix = '$';
}
