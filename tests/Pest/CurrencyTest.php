<?php

use ArchTech\Money\Currency;
use ArchTech\Money\Exceptions\InvalidCurrencyException;
use ArchTech\Money\Tests\Currencies\CZK;

test("a currency is invalid if it doesn't have a name", function () {
    $this->expectException(InvalidCurrencyException::class);

    new Currency(rate: 2.0, code: 'CZK');
});

test("a currency is invalid if it doesn't have a code", function () {
    $this->expectException(InvalidCurrencyException::class);

    new Currency(rate: 2.0, name: 'Czech Crown');
});

test('currencies can be serialized to JSON', function () {
    expect(json_encode(new CZK))->json()->toBe([
        'code' => 'CZK',
        'name' => 'Czech Crown',
        'rate' => 25,
        'prefix' => '',
        'suffix' => ' Kč',
        'mathDecimals' => 2,
        'displayDecimals' => 0,
        'rounding' => 2,
        'decimalSeparator' => ',',
        'thousandsSeparator' => '.',
        'trimTrailingDecimalZeros' => false,
    ]);
});

test('currencies can be created from JSON', function () {
    $original = new Currency(code: 'GBP', rate: 0.8, name: 'British Pound');

    $json = json_encode($original);

    $new = Currency::fromJson($json);

    expect($original->toArray())->toBe($new->toArray());
});
