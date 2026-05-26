<?php

use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

test('cast returns price data type on get', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $price = new Price;
    $price->price = 1000;
    $price->currency_id = $currency->id;
    $price->save();

    expect($price->price)->toBeInstanceOf(PriceDataType::class);
    expect($price->price->value)->toBe(1000);
});

test('cast returns integer on set', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'code' => 'USD',
    ]);

    $price = new Price;
    $price->price = 2500;
    $price->currency_id = $currency->id;
    $price->save();

    expect($price->price->value)->toBe(2500);
});

test('compare price is also casted', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'code' => 'USD',
    ]);

    $price = new Price;
    $price->price = 1000;
    $price->compare_price = 2000;
    $price->currency_id = $currency->id;
    $price->save();

    expect($price->compare_price)->toBeInstanceOf(PriceDataType::class);
    expect($price->compare_price->value)->toBe(2000);
});
