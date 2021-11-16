<?php

use ArchTech\Money\Currencies\USD;
use ArchTech\Money\Money;
use ArchTech\Money\Tests\Currencies\CZK;
use ArchTech\Money\Tests\Currencies\EUR;

test('the currency helper can be used to fetch the current currency', function () {
    expect(currency())->toBe(currencies()->getCurrent());
});

test('the currency helper can be used to fetch a specific currency using its code', function () {
    currencies()->add(EUR::class);

    expect(currency('EUR'))->toBeInstanceOf(EUR::class);
});

test('the currency helper can be used to fetch a specific currency using its class', function () {
    currencies()->add(EUR::class);

    expect(currency(EUR::class))->toBeInstanceOf(EUR::class);
});

test('the money helper creates a new Money instance', function () {
    expect(money(200))->toBeInstanceOf(Money::class);
});

test('the money helper accepts a string currency', function () {
    currencies()->add([EUR::class, CZK::class]);

    expect(money(200, 'EUR')->currency())->toBeInstanceOf(EUR::class);
    expect(money(200, 'CZK')->currency())->toBeInstanceOf(CZK::class);
});

test('the money helper accepts a class currency', function () {
    currencies()->add([EUR::class, CZK::class]);

    expect(money(200, EUR::class)->currency())->toBeInstanceOf(EUR::class);
    expect(money(200, CZK::class)->currency())->toBeInstanceOf(CZK::class);
});

test('the money helper accepts a currency object', function () {
    currencies()->add([EUR::class, CZK::class]);

    expect(money(200, new EUR)->currency())->toBeInstanceOf(EUR::class);
    expect(money(200, new CZK)->currency())->toBeInstanceOf(CZK::class);
});

test('the money helper falls back to the default currency', function () {
    currencies()->add(EUR::class);

    expect(money(200)->currency())->toBeInstanceOf(USD::class);
});
