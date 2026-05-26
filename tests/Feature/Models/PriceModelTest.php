<?php

use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

test('can create price', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 1000,
        'compare_price' => 2000,
        'min_quantity' => 1,
    ]);

    $this->assertDatabaseHas('prices', [
        'id' => $price->id,
        'price' => 1000,
        'compare_price' => 2000,
    ]);
});

test('price is casted to data type', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 1500,
    ]);

    expect($price->price)->toBeInstanceOf(PriceDataType::class);
    expect($price->price->value)->toBe(1500);
    expect($price->price->currency->id)->toBe($currency->id);
});

test('belongs to currency', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 500,
    ]);

    expect($price->currency->is($currency))->toBeTrue();
});

test('default min quantity is one', function () {
    $currency = Currency::factory()->create();

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 1000,
    ]);

    expect($price->min_quantity)->toBe(1);
});

test('compare price is nullable', function () {
    $currency = Currency::factory()->create();

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 1000,
        'compare_price' => null,
    ]);

    expect($price->compare_price)->toBeNull();
});

test('customer group id is nullable', function () {
    $currency = Currency::factory()->create();

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'price' => 1000,
        'customer_group_id' => null,
    ]);

    expect($price->customer_group_id)->toBeNull();
});
