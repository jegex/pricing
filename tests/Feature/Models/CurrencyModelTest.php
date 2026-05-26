<?php

use Jegex\Pricing\Models\Currency;

test('can create currency', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'exchange_rate' => 1.0,
        'decimal_places' => 2,
        'enabled' => true,
        'default' => true,
    ]);

    $this->assertDatabaseHas('currencies', [
        'code' => 'USD',
        'name' => 'US Dollar',
    ]);
});

test('get default returns default currency', function () {
    Currency::factory()->create([
        'code' => 'USD',
        'default' => true,
    ]);
    Currency::factory()->create([
        'code' => 'EUR',
        'default' => false,
    ]);

    $default = Currency::getDefault();

    expect($default->code)->toBe('USD');
});

test('factor for two decimal places', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    expect($currency->factor)->toBe('100');
});

test('factor for zero decimal places', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 0,
    ]);

    expect($currency->factor)->toEqual(1);
});

test('enabled scope', function () {
    Currency::factory()->create([
        'code' => 'USD',
        'enabled' => true,
    ]);
    Currency::factory()->create([
        'code' => 'EUR',
        'enabled' => false,
    ]);

    $enabled = Currency::enabled()->get();

    expect($enabled)->toHaveCount(1);
    expect($enabled->first()->code)->toBe('USD');
});

test('default scope', function () {
    Currency::factory()->create([
        'code' => 'USD',
        'default' => true,
    ]);
    Currency::factory()->create([
        'code' => 'EUR',
        'default' => false,
    ]);

    $defaults = Currency::default()->get();

    expect($defaults)->toHaveCount(1);
    expect($defaults->first()->code)->toBe('USD');
});

test('sync prices defaults to false', function () {
    $currency = Currency::factory()->create();

    expect($currency->sync_prices)->toBeFalse();
});
