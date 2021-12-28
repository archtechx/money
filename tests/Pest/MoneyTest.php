<?php

use ArchTech\Money\CurrencyManager;
use ArchTech\Money\Currencies\USD;
use ArchTech\Money\Currency;
use ArchTech\Money\Money;
use ArchTech\Money\Tests\Currencies\CZK;
use ArchTech\Money\Tests\Currencies\EUR;

test('Money value is immutable', function () {
    pest()->expectError();

    $money = money(100);

    $money->value = 200;
});

test('Money currency is immutable', function () {
    pest()->expectError();

    $money = money(100);

    $money->currency = 'EUR';
});

test('money can be created from a decimal value', function () {
    $money = Money::fromDecimal(10.0, 'USD');

    expect($money->value())->toBe(1000);
});

test('money can be converted to decimals', function () {
    currencies()->add(CZK::class);

    $money = Money::fromDecimal(10.0, 'USD');
    expect($money->value())->toBe(1000);
    expect($money->decimal())->toBe(10.0);

    $money = Money::fromDecimal(15.0, 'CZK');
    expect($money->value())->toBe(1500);
    expect($money->decimal())->toBe(15.0);
});

test('money can be added in base value', function () {
    $money = money(100);
    $money = $money->add(200);

    expect($money->value())->toBe(300);
});

test('money can be added from another Money instance', function () {
    $money = money(100);
    $money = $money->addMoney(money(500));

    expect($money->value())->toBe(600);
});

test('money can be added from a Money instance with a different currency', function () {
    currencies()->add(CZK::class);

    $usd = Money::fromDecimal(10.0, 'USD');
    $czk = Money::fromDecimal(100.0, 'CZK'); // 4 USD

    $usd = $usd->addMoney($czk);

    expect($usd->decimal())->toBe(14.0);
});

test('money can be subtracted in base value', function () {
    $money = money(300);
    $money = $money->subtract(200);

    expect($money->value())->toBe(100);
});

test('money can be subtracted by another Money instance', function () {
    $money = money(500);
    $money = $money->subtractMoney(money(100));

    expect($money->value())->toBe(400);
});

test('money can be subtracted by a Money instance with a different currency', function () {
    currencies()->add(CZK::class);

    $usd = Money::fromDecimal(10.0, 'USD');
    $czk = Money::fromDecimal(100.0, 'CZK'); // 4 USD

    $usd = $usd->subtractMoney($czk);

    expect($usd->decimal())->toBe(6.0);
});

test('money can be multiplied', function () {
    expect(money(100)->multiplyBy(2.5)->value())->toBe(250);

    expect(money(100)->times(2.5)->value())->toBe(250);
});

test('money can be divided', function () {
    expect(money(100)->divideBy(10)->value())->toBe(10);
});

test('fees can be added to and subtracted from money', function () {
    $money = Money::fromDecimal(10.0);

    expect($money->addFee(10)->decimal())->toBe(11.0);
    expect($money->subtractFee(10)->decimal())->toBe(9.09); // 10/1.1
});

test('taxes can be added and subtracted from money', function () {
    currencies()->add([CZK::class]);

    expect(
        Money::fromDecimal(100.0, 'CZK')->addTax(21.0)->decimal()
    )->toBe(121.0);

    expect(
        Money::fromDecimal(121.0, 'CZK')->subtractTax(21.0)->decimal()
    )->toBe(100.0);
});

test('money can be converted to a different currency', function () {
    currencies()->add([CZK::class]);

    $money = Money::fromDecimal(100.0);

    expect($money->currency())->toBeInstanceOf(USD::class);
    expect($money->currency()->code())->toBe('USD');

    $money = $money->convertTo(CZK::class);

    expect($money->decimal())->toBe(2500.0);
    expect($money->currency())->toBeInstanceOf(CZK::class);
    expect($money->currency()->code())->toBe('CZK');
});

test('money can be formatted', function () {
    expect(
        Money::fromDecimal(10.00, USD::class)->formatted()
    )->toBe('$10.00');
});

test('money can be formatted without rounding', function () {
    currencies()->add([CZK::class]);

    expect(
        Money::fromDecimal(10.34, CZK::class)->rawFormatted()
    )->toBe('10,34 Kč');
});

test('converting money to a string returns the formatted string', function () {
    expect(
        (string) Money::fromDecimal(10.00, USD::class)
    )->toBe('$10.00');
});

test('money can be converted to default currency value', function () {
    currencies()->add(CZK::class);

    $money = money(5000, CZK::class);

    expect($money->valueInDefaultCurrency())->toBe(
        $money->convertTo(currencies()->getDefault())->value()
    );
});

test('money can have rounding', function () {
    currencies()->add([CZK::class, EUR::class]);

    expect(money(12340, 'CZK')->rounding())->toBe(-40);
    expect(money(12340, 'EUR')->rounding())->toBe(0);
});

test('money can be rounded with a custom precision', function () {
    currencies()->add(CZK::class);

    expect(money(12340, 'CZK')->rounding())->toBe(-40);
    expect(money(12340, 'CZK')->rounded()->value())->toBe(12300);
    expect(money(12340, 'CZK')->rounded(3)->value())->toBe(12000);
});

test('money can be compared', function () {
    expect(
        money(123)->equals(money(123))
    )->toBeTrue();

    expect(
        money(123)->equals(money(456))
    )->toBeFalse();
});

test('money can be compared with different currencies', function () {
    currencies()->add(CZK::class);

    expect(
        money(123, 'USD')->equals(money(123)->convertTo(CZK::class))
    )->toBeTrue();
});

test('the is method compares both the value and the currency', function () {
    currencies()->add([CZK::class, EUR::class]);

    expect(
        money(123, 'EUR')->is(money(123)->convertTo(CZK::class))
    )->toBeFalse();

    expect(
        money(123, 'EUR')->is(money(123)->convertTo(CZK::class)->convertTo(EUR::class))
    )->toBeFalse();
});

test('the cents from the decimal value can be fetched using the cents method', function () {
    currencies()->add(CZK::class);

    expect(money(100)->cents())->toBeInstanceOf(Money::class);

    expect(money(1234, USD::class)->cents()->value())->toBe(34);
    expect(money(1234, CZK::class)->cents()->value())->toBe(34);

    expect(money(100, USD::class)->cents()->value())->toBe(0);
    expect(money(100, CZK::class)->cents()->value())->toBe(0);

    expect(money(123456789, USD::class)->cents()->value())->toBe(89);
    expect(money(123456789, CZK::class)->cents()->value())->toBe(89);
});

test('decimal values can be added and subtracted', function () {
    expect(
        money(1234)->addDecimal(21.3)->value()
    )->toBe(3364);

    expect(
        money(1234)->subtractDecimal(8.3)->value()
    )->toBe(404);
});

test('money can be serialized to JSON', function () {
    currencies()->add(CZK::class);

    $money = Money::fromDecimal(22, 'CZK');

    expect(json_encode($money))->json()->toBe(['value' => 2200, 'currency' => 'CZK']);
    expect($money->toJson())->json()->toBe(['value' => 2200, 'currency' => 'CZK']);
});

test('money can be instantiated from JSON', function () {
    currencies()->add(CZK::class);

    $original = Money::fromDecimal(22, 'CZK');

    $json = json_encode($original);

    $new = Money::fromJson($json)->toArray();

    expect($new)->toBe($original->toArray());
});
