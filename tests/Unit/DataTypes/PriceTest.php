<?php

use Jegex\Pricing\DataTypes\Price;
use Jegex\Pricing\Models\Currency;

test('can construct price with int value', function () {
    $currency = Currency::factory()->create(['decimal_places' => 2]);
    $price = new Price(1000, $currency);

    expect($price->value)->toBe(1000);
    expect($price->currency)->toBe($currency);
    expect($price->unitQty)->toBe(1);
});

test('throws exception for non int value', function () {
    $currency = Currency::factory()->create();

    $this->expectException(\TypeError::class);

    new Price('not-an-int', $currency);
});

test('can construct with unit qty', function () {
    $currency = Currency::factory()->create(['decimal_places' => 2]);
    $price = new Price(2000, $currency, 2);

    expect($price->unitQty)->toBe(2);
});

test('to string returns value', function () {
    $currency = Currency::factory()->create(['decimal_places' => 2]);
    $price = new Price(500, $currency);

    expect((string) $price)->toBe('500');
});

test('decimal conversion', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
        'code' => 'USD',
    ]);
    $price = new Price(12345, $currency);

    expect($price->decimal())->toBe(123.45);
});

test('decimal without rounding', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 3,
        'code' => 'USD',
    ]);
    $price = new Price(12345, $currency);

    expect($price->decimal(false))->toBe(12.345);
});

test('unit decimal', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
        'code' => 'USD',
    ]);
    $price = new Price(2000, $currency, 2);

    expect($price->unitDecimal())->toBe(10.0);
});
