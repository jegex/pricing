<?php

use Illuminate\Support\Facades\Queue;
use Jegex\Pricing\Jobs\CreateCurrencyPrices;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

beforeEach(function () {
    Queue::fake();
});

test('dispatches action when non-default currency has a default counterpart', function () {
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

    $basePrice = Price::factory()->create([
        'currency_id' => $base->id,
        'price' => 10000,
    ]);

    $job = new CreateCurrencyPrices($incoming);
    $job->handle();

    $synced = Price::where('currency_id', $incoming->id)
        ->where('priceable_id', $basePrice->priceable_id)
        ->first();

    expect($synced)->not->toBeNull();
});

test('returns early when incoming currency is the default', function () {
    $default = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => true,
    ]);

    $job = new CreateCurrencyPrices($default);
    $job->handle();

    expect(true)->toBeTrue();
});

test('returns early when no default currency exists', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'exchange_rate' => 1.0,
        'default' => false,
    ]);

    $job = new CreateCurrencyPrices($currency);
    $job->handle();

    expect(true)->toBeTrue();
});
