<?php

namespace ArchTech\Money\Tests\Currencies;

use ArchTech\Money\Currency;

class EUR extends Currency
{
    protected string $code = 'EUR';
    protected string $name = 'Euro';
    protected float $rate = 0.9;
    protected int $mathDecimals = 4;
    protected int $displayDecimals = 2;
    protected int $rounding = 0;
    protected string $suffix = ' €';
}
