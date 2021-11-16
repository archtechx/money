<?php

use ArchTech\Money\Currencies\USD;
use ArchTech\Money\Currency;
use ArchTech\Money\Tests\Currencies\CZK;
use ArchTech\Money\Tests\Currencies\EUR;

beforeEach(fn () => currencies()->reset());

test('only USD is loaded by default', function () {
    expect(currencies()->all())
        ->toHaveCount(1)
        ->toHaveKey('USD');
});

test('USD is the default currency', function () {
    expect(currencies()->getDefault())->toBeInstanceOf(USD::class);
});

test('USD is the current currency', function () {
    expect(currencies()->getCurrent())->toBeInstanceOf(USD::class);
});

test('the default currency can be changed', function () {
    currencies()->add([CZK::class, EUR::class]);

    expect(currencies()->getDefault())->toBeInstanceOf(USD::class);

    // Using class
    currencies()->setDefault(CZK::class);
    expect(currencies()->getDefault())->toBeInstanceOf(CZK::class);

    // Using code
    currencies()->setDefault('EUR');
    expect(currencies()->getDefault())->toBeInstanceOf(EUR::class);
});

test('the current currency can be changed', function () {
    currencies()->add([CZK::class, EUR::class]);

    expect(currencies()->getCurrent())->toBeInstanceOf(USD::class);

    // Using class
    currencies()->setCurrent(CZK::class);
    expect(currencies()->getCurrent())->toBeInstanceOf(CZK::class);

    // Using code
    currencies()->setCurrent('EUR');
    expect(currencies()->getCurrent())->toBeInstanceOf(EUR::class);
});

test('the current currency can be persisted', function () {
    currencies()->add([CZK::class, EUR::class]);

    $store = [];
    $resolverCalled = false;

    currencies()->storeCurrentUsing(function ($currency) use (&$store) {
        $store[] = $currency;
    });

    currencies()->resolveCurrentUsing(function () use (&$resolverCalled, &$store) {
        $resolverCalled = true;

        return last($store);
    });

    currencies()->setCurrent('CZK');
    expect($store)->toBe(['CZK']);
    expect(currencies()->getCurrent()->code())->toBe('CZK');
    expect($resolverCalled)->toBeFalse();

    currencies()->setCurrent('EUR');
    expect($store)->toBe(['CZK', 'EUR']);
    expect(currencies()->getCurrent()->code())->toBe('EUR');
    expect($resolverCalled)->toBeFalse();

    currencies()->forgetCurrent();

    expect(currencies()->getCurrent()->code())->toBe('EUR');
    expect($resolverCalled)->toBeTrue(); // got called
});

test('individual currencies can be removed', function () {
    currencies()->add(CZK::class);

    expect(currencies()->all())->toHaveCount(2);

    currencies()->remove('USD');

    expect(currencies()->all())->toHaveCount(1);
});

test('currencies can be cleared', function () {
    currencies()->clear();
    expect(currencies()->all())->toHaveCount(0);
});

test('more currencies can be provided', function () {
    currencies()->add([CZK::class, EUR::class]);

    expect(currencies()->all())->toHaveCount(3);
});

test('duplicate currencies get overriden', function () {
    $customUSD = new USD(rate: 20);

    currencies()->add($customUSD);

    expect(currencies()->all())->toHaveCount(1);
    expect(currencies()->all())->toHaveKey('USD', $customUSD);
});

test('the default currency can have any rate', function () {
    // This tests that the default currency doesn't have to have a rate of 1
    currencies()->add([CZK::class, EUR::class]);

    expect(
        money(1500, 'CZK')->convertTo(EUR::class)->formatted()
    )->toBe('0.54 €');

    currencies()->setDefault(CZK::class);

    expect(
        money(1500, 'CZK')->convertTo(EUR::class)->formatted()
    )->toBe('0.54 €');
});

test('the getCode method accepts any currency format', function () {
    expect(currencies()->getCode(USD::class))->toBe('USD');
    expect(currencies()->getCode(new USD))->toBe('USD');
    expect(currencies()->getCode('USD'))->toBe('USD');
});

test('array currencies get converted to anonymous Currency objects', function () {
    currencies()->add((new CZK)->toArray());

    expect(currencies()->all())->toHaveKey('CZK');
});

test('add accepts any currency format', function () {
    currencies()->clear();

    // Class instances
    currencies()->add($instance = new CZK);
    expect(currencies()->all())->toHaveKey('CZK', $instance);

    // Anonymous class instances
    currencies()->add($gbp = new Currency(code: 'GBP', rate: 0.8, name: 'British Pound'));
    expect(currencies()->all())->toHaveKey('GBP', $gbp);

    // Class names
    currencies()->add(USD::class);
    expect(currencies()->all())->toHaveKey('USD');

    // Arrays
    currencies()->add((new CZK)->toArray());
    expect(currencies()->all())->toHaveKey('CZK');
});

test('the add method accepts an array of named Currency instances', function () {
    currencies()->add([
        new CZK,
        new EUR,
    ]);

    expect(currencies()->all())->toHaveKeys(['CZK', 'EUR']);
});

test('the add method accepts an array of anonymous Currency instances', function () {
    currencies()->add([
        new Currency(code: 'USD', rate: 1, name: 'United States Dollar'),
        new Currency(code: 'EUR', rate: 0.8, name: 'Euro'),
    ]);

    expect(currencies()->all())->toHaveKeys(['USD', 'EUR']);
});

test('the add method accepts an array of Currency class strings', function () {
    currencies()->add([USD::class, EUR::class]);

    expect(currencies()->all())->toHaveKeys(['USD', 'EUR']);
});

test('the add method accepts an array of currency arrays', function () {
    currencies()->add([
        (new CZK)->toArray(),
        (new EUR)->toArray(),
    ]);

    expect(currencies()->all())->toHaveKeys(['USD', 'CZK', 'EUR']);
});
