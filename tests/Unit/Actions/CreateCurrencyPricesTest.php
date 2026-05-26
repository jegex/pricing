<?php

use Illuminate\Support\Facades\DB;
use Jegex\Pricing\Actions\CreateCurrencyPrices;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

test('copies prices from base currency with exchange rate', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
    ]);
    $incoming = Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
        'compare_price' => 12000,
        'min_quantity' => 1,
    ]);

    (new CreateCurrencyPrices)->handle($incoming, $base);

    $synced = Price::where('currency_id', $incoming->id)->first();

    expect($synced)->not->toBeNull();
    expect($synced->price->value)->toBe((int) round(10000 * 0.79));
    expect($synced->compare_price->value)->toBe((int) round(12000 * 0.79));
    expect($synced->priceable_type)->toBe($price->priceable_type);
    expect($synced->priceable_id)->toBe($price->priceable_id);
    expect($synced->customer_group_id)->toBe($price->customer_group_id);
    expect($synced->min_quantity)->toBe(1);
});

test('copies prices with null compare price', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
    ]);
    $incoming = Currency::factory()->create([
        'code' => 'EUR',
        'exchange_rate' => 0.85,
        'default' => false,
    ]);

    Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 5000,
        'compare_price' => null,
        'min_quantity' => 1,
    ]);

    (new CreateCurrencyPrices)->handle($incoming, $base);

    $synced = Price::where('currency_id', $incoming->id)->first();

    expect($synced)->not->toBeNull();
    expect($synced->price->value)->toBe((int) round(5000 * 0.85));
    expect($synced->compare_price)->toBeNull();
});

test('does nothing when base currency has no prices', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
    ]);
    $incoming = Currency::factory()->create([
        'code' => 'JPY',
        'exchange_rate' => 110.0,
        'default' => false,
    ]);

    (new CreateCurrencyPrices)->handle($incoming, $base);

    $count = Price::where('currency_id', $incoming->id)->count();

    expect($count)->toBe(0);
});
