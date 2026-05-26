<?php

use Illuminate\Support\Facades\Queue;
use Jegex\Pricing\Jobs\SyncPriceCurrencies;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

beforeEach(function () {
    Queue::fake();
});

test('dispatches sync job on create for default currency price', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => true,
    ]);
    Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => true,
    ]);

    $price = new Price;
    $price->price = 1000;
    $price->currency_id = $base->id;
    $price->save();

    Queue::assertPushed(SyncPriceCurrencies::class);
});

test('does not dispatch sync job on create for non-default currency', function () {
    $nonDefault = Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => true,
    ]);

    $price = new Price;
    $price->price = 1000;
    $price->currency_id = $nonDefault->id;
    $price->save();

    Queue::assertNotPushed(SyncPriceCurrencies::class);
});

test('does not dispatch sync job when no currencies have sync_prices enabled', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => false,
    ]);

    $price = new Price;
    $price->price = 1000;
    $price->currency_id = $base->id;
    $price->save();

    Queue::assertNotPushed(SyncPriceCurrencies::class);
});

test('dispatches sync job on update', function () {
    $base = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
        'sync_prices' => true,
    ]);
    Currency::factory()->create([
        'code' => 'GBP',
        'exchange_rate' => 0.79,
        'default' => false,
        'sync_prices' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 1000,
    ]);

    Queue::assertPushed(SyncPriceCurrencies::class);

    Queue::fake();

    $price->update(['price' => 2000]);

    Queue::assertPushed(SyncPriceCurrencies::class);
});
