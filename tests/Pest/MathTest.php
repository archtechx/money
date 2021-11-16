<?php

use ArchTech\Money\CurrencyManager;
use ArchTech\Money\Tests\Currencies\CZK;

test('money is rounded to its math decimals after each operation', function () {
    expect(
        money(33)->times(3.03)->value() // 0.9999
    )->toBe(100);
});

test('operations use the math decimals even if the display decimals are different', function () {
    currencies()->add(CZK::class); // uses 0 display decimals

    $money = money(33, 'CZK')->times(3); // 0.99
    expect($money->value())->toBe(99);
    expect($money->formatted())->toBe('1 Kč');

    $money = money(33, 'CZK')->times(3.03); // 0.9999
    expect($money->value())->toBe(100);
    expect($money->formatted())->toBe('1 Kč');
});

test('rounding is not applied between operations', function () {
    currencies()->add(CZK::class);

    // 99
    $money = money(33, 'CZK')->times(3);
    expect($money->value())->toBe(99);
    expect($money->rounded()->value())->toBe(100);
    expect($money->rounding())->toBe(1);

    // 139
    $money = $money->add(40);
    expect($money->value())->toBe(139);
    expect($money->rounded()->value())->toBe(100);
    expect($money->rounding())->toBe(-39);

    // 151
    $money = $money->add(12);
    expect($money->value())->toBe(151);
    expect($money->rounded()->value())->toBe(200);
    expect($money->rounding())->toBe(49);
});
