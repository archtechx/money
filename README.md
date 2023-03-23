# Money

A simple package for working with money.

Main features:
- Simple API
- Livewire integration
- Custom currency support
- Highly customizable formatting
- Rounding logic for compliant accounting

This package is our implementation of the [Money pattern](https://martinfowler.com/eaaCatalog/money.html).

You can read more about why we built it and how it works on our forum: [New package: archtechx/money](https://forum.archte.ch/archtech/t/new-package-archtechxmoney).

## Installation

Require the package via composer:
```sh
composer require archtechx/money
```
# Usage

The package has two main classes:
- `Money` which represents monetary values
- `Currency` which is extended by the currencies that you're using

This document uses the terms [decimal value](#decimal-value), [base value](#base-value), [default currency](#default-currency), [current currency](#current-currency), [rounding](#rounding), [math decimals](#math-decimals), [display decimals](#display-decimals), and a few others. Refer to the [Terminology](#terminology) section for definitions.

## Money

**Important**: As an implementation of the [Money pattern](https://martinfowler.com/eaaCatalog/money.html), the `Money` object creates a new instance after each operation. Meaning, **all `Money` instances are immutable**. To modify the value of a variable, re-initialize it with a new value:

```php
// Incorrect
$money = money(1500);
$money->times(3); // ❌
$money->value(); // 1500

// Correct
$money = money(1500);
$money = $money->times(3); // ✅
$money->value(); // 4500
```

### Creating `Money` instances
```php
// Using cents
$money = money(1500); // $15.00; default currency
$money = money(1500, 'EUR'); // 15.00 €
$money = money(2000, new USD); // $20.00
$money = money(3000, CZK::class); // 30 Kč

// Using decimals
$money = Money::fromDecimal(15.00, 'EUR'); // 15.00 €
$money = Money::fromDecimal(20.00, new USD); // $20.00
$money = Money::fromDecimal(30.00, CZK::class); // 30 Kč
```

### Arithmetics

```php
// Addition
$money = money(1000);
$money = $money->add(500);
$money->value(); // 1500

// Subtraction
$money = money(1000);
$money = $money->subtract(500);
$money->value(); // 500

// Multiplication
$money = money(1000);
$money = $money->multiplyBy(2); // alias: ->times()
$money->value(); // 2000

// Division
$money = money(1000);
$money = $money->divideBy(2);
$money->value(); // 500
```

### Converting money to a different currency

```php
$money = money(2200);
$money->convertTo(CZK::class);
```

### Comparing money instances

**Equality of monetary value**
```php
// Assuming CZK is 25:1 USD

// ✅ true
money(100, USD::class)->equals(money(100, USD::class));

// ❌ false
money(100, USD::class)->equals(money(200, USD::class));

// ✅ true
money(100, USD::class)->equals(money(2500, CZK::class));

// ❌ false
money(100, USD::class)->equals(money(200, CZK::class));
```

**Equality of monetary value AND currency**
```php
// Assuming CZK is 25:1 USD

// ✅ true
money(100, USD::class)->is(money(100, USD::class));

// ❌ false: different monetary value
money(100, USD::class)->is(money(200, USD::class));

// ❌ false: different currency
money(100, USD::class)->is(money(2500, CZK::class));

// ❌ false: different currency AND monetary value
money(100, USD::class)->is(money(200, CZK::class));
```

### Adding fees

You can use the `addFee()` or `addTax()` methods to add a % fee to the money:
```php
$money = money(1000);
$money = $money->addTax(20.0); // 20%
$money->value(); // 1200
```

### Accessing the decimal value

```php
$money = Money::fromDecimal(100.0, new USD);
$money->value(); // 10000
$money->decimal(); // 100.0
```

### Formatting money

You can format money using the `->formatted()` method. It takes [display decimals](#display-decimals) into consideration.

```php
$money = Money::fromDecimal(40.25, USD::class);
$money->formatted(); // $40.25
```

The method optionally accepts overrides for the [currency specification](#currency-logic):
```php
$money = Money::fromDecimal(40.25, USD::class);

// $ 40.25 USD
$money->formatted(decimalSeparator: ',', prefix: '$ ', suffix: ' USD');
```

The overrides can also be passed as an array:
```php
$money = Money::fromDecimal(40.25, USD::class);

// $ 40.25 USD
$money->formatted(['decimalSeparator' => ',', 'prefix' => '$ ', 'suffix' => ' USD']);
```

There's also `->rawFormatted()` if you wish to use [math decimals](#math-decimals) instead of [display decimals](#display-decimals).
```php
$money = Money::new(123456, CZK::class);
$money->rawFormatted(); // 1 234,56 Kč
```

Converting the formatted value back to the `Money` instance is also possible. The package tries to extract the currency from the provided string:
```php
$money = money(1000);
$formatted = $money->formatted(); // $10.00
$fromFormatted = Money::fromFormatted($formatted);
$fromFormatted->is($money); // true
```

If you had passed overrides while [formatting the money instance](#formatting-money), the same can passed to this method.
```php
$money = money(1000);
$formatted = $money->formatted(['prefix' => '$ ', 'suffix' => ' USD']); // $ 10.00 USD
$fromFormatted = Money::fromFormatted($formatted, USD::class, ['prefix' => '$ ', 'suffix' => ' USD']);
$fromFormatted->is($money); // true
```

Notes:
1) If currency is not specified and none of the currencies match the prefix and suffix, an exception will be thrown.
2) If currency is not specified and multiple currencies use the same prefix and suffix, an exception will be thrown.
3) `fromFormatted()` misses the cents if the [math decimals](#math-decimals) are greater than [display decimals](#display-decimals).

### Rounding money

Some currencies, such as the Czech Crown (CZK), generally display final prices in full crowns, but use cents for the intermediate math operations. For example:

```php
$money = Money::fromDecimal(3.30, CZK::class);
$money->value(); // 330
$money->formatted(); // 3 Kč

$money = $money->times(3);
$money->value(); // 990
$money->formatted(); // 10 Kč
```

If the customer purchases a single `3.30` item, he pays `3 CZK`, but if he purchases three `3.30` items, he pays `10 CZK`.

This rounding (to full crowns) is standard and legal per the accounting legislation, since it makes payments easier. However, the law requires you to keep track of the rounding difference for tax purposes.

#### Getting the used rounding

For that use case, our package lets you get the rounding difference using a simple method call:
```php
$money = Money::fromDecimal(9.90, CZK::class);
$money->decimal(); // 9.90
$money->formatted(); // 10 Kč
$money->rounding(); // +0.10 Kč = 10

$money = Money::fromDecimal(3.30, CZK::class);
$money->decimal(); // 3.30
$money->formatted(); // 3 Kč
$money->rounding(); // -0.30 Kč = -30
```

#### Applying rounding to money

```php
// Using the currency rounding
$money = Money::fromDecimal(9.90, CZK::class);
$money->decimal(); // 9.90
$money = $money->rounded(); // currency rounding
$money->decimal(); // 10.0

// Using custom rounding
$money = Money::fromDecimal(2.22, USD::class);
$money->decimal(); // 2.22
$money = $money->rounded(1); // custom rounding: 1 decimal
$money->decimal(); // 2.20
```

## Currencies

To work with the registered currencies, use the bound `CurrencyManager` instance, accessible using the `currencies()` helper.

### Creating a currency

This package provides only USD currency by default.

You can create a currency using one of the multiple supported syntaxes.
```php
// anonymous Currency object
$currency = new Currency(
    code: 'FOO',
    name: 'Foo currency',
    rate: 1.8,
    prefix: '# ',
    suffix: ' FOO',
);

// array
$currency = [
    'code' => 'FOO',
    'name' => 'Foo currency',
    'rate' => 1.8,
    'prefix' => '# ',
    'suffix' => ' FOO',
];

// class
class FOO extends Currency
{
    protected string $code = 'FOO';
    protected string $name = 'Foo currency';
    protected float $rate = 1.8;
    protected string $prefix = '# ';
    protected string $suffix = ' FOO';
}
```

See the [Currency logic](#currency-logic) section for a list of available properties to configure. Note that when registering a currency, two values **must** be specified:
1. The code of the currency (e.g. `USD`)
2. The name of the currency (e.g. `United States Dollar`)

### Adding a currency

Register a new currency:
```php
currencies()->add(new USD);
currencies()->add(USD::class);
currencies()->add($currency); // object or array
```

### Removing a specific currency

To remove a specific currency, you can use the `remove()` method:
```php
currencies()->remove('USD');
currencies()->remove(USD::class);
```

### Removing all currencies

To remove all currencies, you can use the `clear()` method:
```php
currencies()->clear();
```

### Resetting currencies

Can be useful in tests. This reverts all your changes and makes the `CurrencyManager` use `USD` as the default currency.

```php
currencies()->reset();
```

### Currency logic

Currencies can have the following properties:
```php
protected string $code = null;
protected string $name = null;
protected float $rate = null;
protected string $prefix = null;
protected string $suffix = null;
protected int $mathDecimals = null;
protected int $displayDecimals = null;
protected int $rounding = null;
protected string $decimalSeparator = null;
protected string $thousandsSeparator = null;
```

For each one, there's also a `public` method. Specifying a method can be useful when your currency config is dynamic, e.g. when the currency rate is taken from some API:

```php
public function rate(): float
{
    return cache()->remember("{$this->code}.rate", 3600, function () {
        return Http::get("https://api.currency.service/rate/USD/{$this->code}");
    });
}
```

### Setting the default currency

You can set the [default currency](#default-currency) using the `setDefault()` method:
```php
currencies()->setDefault('USD');
```

### Setting the current currency

You can set the [current currency](#current-currency) using the `setCurrent()` method:
```php
currencies()->setCurrent('USD');
```

### Persisting a selected currency across requests

If your users can select the currency they want to see the app in, the package can automatically write the current currency to a persistent store of your choice, and read from that store on subsequent requests.

For example, say we want to use the `currency` session key to keep track of the user's selected session. To implement that, we only need to do this:
```php
currencies()
    ->storeCurrentUsing(fn (string $code) => session()->put('currency', $code))
    ->resolveCurrentUsing(fn () => session()->get('currency'));
```
You can add this code to your AppServiceProvider's `boot()` method.

Now, whenever the current currency is changed using `currencies()->setCurrent()`, perhaps in a route like this:
```php
Route::get('/currency/change/{currency}', function (string $currency) {
    currencies()->setCurrent($currency);

    return redirect()->back();
});
```
it will also be written to the `currency` session key. The route can be used by a `<form>` in your navbar, or any other UI element.

# Terminology

This section explains the terminology used in the package.

## Values

Multiple different things can be meant by the "value" of a `Money` object. For that reason, we use separate terms.

### Base value

The base value is the value passed to the `money()` helper:
```php
$money = money(1000);
```
and returned from the `->value()` method:
```php
$money->value(); // 1000
```

This is the actual integer value of the money. In most currencies this will be the cents.

The package uses the base value for all money calculations.

### Decimal value

The decimal value isn't used for calculations, but it is the human-readable one. It's typically used in the formatted value.
```php
$money = Money::fromDecimal(100.0); // $100 USD
$money->value(); // 10000
$money->decimal(); // 100.0
```

### Value in default currency

This is the value of a `Money` object converted to the default currency.

For example, you may want to let administrators enter the price of a product in any currency, but still store it in the default currency.

It's generally recommended to use the default currency in the "code land". And only use other currencies for displaying prices to the user (e.g. customer) or letting the administrators enter prices of things in a currency that works for them.

Of course, there are exceptions, and sometimes you may want to store both the currency and the value of an item. For that, the package has [JSON encoding features](#json-serialization) if you wish to store the entire `Money` object in a single database column.

Storing the integer price and the string currency as separate columns is, of course, perfectly fine as well.

### Formatted value

The formatted value is the Money value displayed per its currency spec. It may use the prefix, suffix, decimal separator, thousands separator, and the [display decimals](#display-decimals).

For example:
```php
money(123456, new CZK)->formatted(); // 1 235 Kč
```

Note that the [display decimals](#display-decimals) can be different from the [math decimals](#math-decimals).

For the Czech Crown (CZK), the display decimals will be `0`, but the math decimals will be `2`. Meaning, cents are used for money calculations, and the `decimal()` method will return the base value divided by `100`, but the display decimals don't include any cents.

### Raw formatted value

For the inverse of what was just explained above, you can use the `rawFormatted()` method. This returns the formatted value, **but uses the math decimals for the display decimals**. Meaning, the value in the example above will be displayed including cents:
```php
money(123456, new CZK)->rawFormatted(); // 1 234,56 Kč
```

This is mostly useful for currencies like the Czech Crown which generally don't use cents, but **can** use them in specific cases.

## Currencies

### Current currency

The current currency refers to the currently used currency.

By default, the package doesn't use it anywhere. All calls such as `money()` will use the provided currency, or the default currency.

The current currency is something you can convert money to in the final step of calculations, right before displaying it to the user in the browser.

### Default currency

The default currency is the currency that Money defaults to in the context of your codebase.

The `money()` helper, `Money::fromDecimal()` method, and `new Money()` all use this currency (unless a specific one is provided).

It can be a good idea to use the default currency for data storage. See more about this in the [Value in default currency](#value-in-default-currency) section.

### Math decimals

The math decimals refer to the amount of decimal points the currency has in a math context.

All math operations are still done in floats, using the [base value](#base-value), but the math decimals are used for knowing how to round the money after each operation, how to instantiate it with the `Money::fromDecimal()` method, and more.

### Display decimals

The display decimals refer to the amount of decimals used in the [formatted value](#formatted-value).

# Extra features

## Livewire support

The package supports Livewire out of the box. You can typehint any Livewire property as `Money` and the monetary value & currency will be stored in the component's state.

```php
class EditProduct extends Component
{
    public Money $price;

    // ...
}
```

Livewire's custom type support isn't advanced yet, so this is a bit harder to use in the Blade view — a wrapper Alpine component is recommended. In a future release, `wire:model` will be supported for `currency` and `value` directly.

The component can look roughly like this:
```html
<div x-data="{
    money: {
        value: {{ $price->decimal() }},
        currency: {{ $price->currency()->code() }},
    },

    init() {
        $watch('money', money => $wire.set('money', {
            value: Math.round(money.value / 100),
            currency: money.currency.
        }))
    },
}" x-init="init">
    Currency: <select x-model="currency">...</select>
    Price: <input x-model="value" type="number" step="0.01">
</div>
```

## JSON serialization

Both currencies and `Money` instances can be converted to JSON, and instantiated from JSON.

```php
$currency = new CZK;
$json = json_encode($currency);
$currency = Currency::fromJson($json);

$foo = money(100, 'CZK');
$bar = Money::fromJson($money->toJson());
$money->is($bar); // true
```

## Tips

### 💡 Accepted currency code formats

Most methods which accept a currency accept it in any of these formats:
```php
currency(USD::class);
currency(new USD);
currency('USD');

money(1000, USD::class)->convertTo('CZK');
money(1000, 'USD')->convertTo(new CZK);
money(1000, new USD)->convertTo(CZK::class);
```

### 💡 Dynamically add currencies

Class currencies are elegant, but not necessary. If your currency specs come from the database, or some API, you can register them as arrays.

```php
// LoadCurrencies middleware

currencies()->add(cache()->remember('currencies', 3600, function () {
    return UserCurrencies::where('user_id', auth()->id())->get()->toArray();
});
```

Where the DB call returns an array of array currencies following the [format mentioned above](#creating-a-currency).

## Development & contributing

Run all checks locally:

```sh
./check
```

Code style will be automatically fixed by php-cs-fixer.

No database is needed to run the tests.
