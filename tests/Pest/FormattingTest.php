<?php

use ArchTech\Money\Currencies\USD;
use ArchTech\Money\Currency;
use ArchTech\Money\Money;
use ArchTech\Money\Tests\Currencies\CZK;
use ArchTech\Money\Tests\Currencies\EUR;
use ArchTech\Money\Tests\Currencies\SEK;

beforeEach(fn () => currencies()->add([CZK::class, EUR::class, SEK::class]));

test('prefixes are applied', function () {
    expect(Money::fromDecimal(10.00, USD::class)->formatted())->toBe('$10.00');
});

test('suffixes are applied', function () {
    expect(Money::fromDecimal(10.00, CZK::class)->formatted())->toBe('10 Kč');
});

test('decimals can be applied even if the decimal points are zero', function () {
    expect(Money::fromDecimal(10.00, CZK::class)->formatted())->toBe('10 Kč');

    expect(Money::fromDecimal(10.00, EUR::class)->formatted())->toBe('10.00 €');
});

test('decimals have a separator', function () {
    expect(Money::fromDecimal(10.34, EUR::class)->formatted())->toBe('10.34 €');

    expect(Money::fromDecimal(10.34, CZK::class)->rawFormatted())->toBe('10,34 Kč');
});

test('thousands have a separator', function () {
    currencies()->add(new Currency(
        code: 'FOO',
        name: 'Foo Currency',
        thousandsSeparator: ' ',
    ));

    expect(Money::fromDecimal(1234567.89, 'USD')->formatted())->toBe('$1,234,567.89');
    expect(Money::fromDecimal(1234567.89, 'EUR')->formatted())->toBe('1,234,567.89 €');
    expect(Money::fromDecimal(1234567.89, 'CZK')->formatted())->toBe('1.234.568 Kč');
    expect(Money::fromDecimal(1234567.89, 'FOO')->formatted())->toBe('1 234 567.89');
});

test('the format method accepts overrides', function () {
    expect(Money::fromDecimal(10.45)->formatted(['decimalSeparator' => ',', 'prefix' => '$$$']))->toBe('$$$10,45');
    expect(Money::fromDecimal(10.45)->formatted(decimalSeparator: ',', suffix: ' USD'))->toBe('$10,45 USD');
});

test('the trailing decimal zeros trimming', function () {
    expect(Money::fromDecimal(10.00, SEK::class)->formatted())->toBe('10 kr');
    expect(Money::fromDecimal(10.10, SEK::class)->formatted())->toBe('10.1 kr');
    expect(Money::fromDecimal(10.12, SEK::class)->formatted())->toBe('10.12 kr');

    expect(Money::fromDecimal(10.00, EUR::class)->formatted())->toBe('10.00 €');
    expect(Money::fromDecimal(10.10, EUR::class)->formatted())->toBe('10.10 €');
    expect(Money::fromDecimal(10.12, EUR::class)->formatted())->toBe('10.12 €');
});