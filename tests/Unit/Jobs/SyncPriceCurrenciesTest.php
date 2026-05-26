<?php

use Illuminate\Support\Facades\Queue;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;
use Jegex\Pricing\Jobs\SyncPriceCurrencies;

test('creates counterpart price when none exists', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => true,
    ]);
    $target = Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
        'compare_price' => 12000,
    ]);

    (new SyncPriceCurrencies($price))->handle();

    $synced = Price::where('currency_id', $target->id)
        ->where('priceable_id', $price->priceable_id)
        ->first();

    expect($synced)->not->toBeNull();
    expect($synced->price->value)->toBe(7900);
    expect($synced->compare_price->value)->toBe(9480);
});

test('updates existing counterpart price', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => true,
    ]);
    $target = Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
    ]);

    Price::factory()->create([
        'currency_id' => $target->id,
        'price' => 5000,
        'priceable_id' => $price->priceable_id,
        'priceable_type' => $price->priceable_type,
        'customer_group_id' => $price->customer_group_id,
        'min_quantity' => $price->min_quantity,
    ]);

    (new SyncPriceCurrencies($price))->handle();

    $updated = Price::where('currency_id', $target->id)
        ->where('priceable_id', $price->priceable_id)
        ->first();

    expect($updated->price->value)->toBe(7900);
});

test('skips currencies without sync_prices enabled', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => false,
    ]);
    Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => false,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
    ]);

    (new SyncPriceCurrencies($price))->handle();

    $count = Price::where('currency_id', '!=', $base->id)->count();

    expect($count)->toBe(0);
});

test('skips source currency itself', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
    ]);

    (new SyncPriceCurrencies($price))->handle();

    $all = Price::count();

    expect($all)->toBe(1);
});
