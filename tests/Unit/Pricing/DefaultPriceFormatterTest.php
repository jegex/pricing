<?php

use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Pricing\DefaultPriceFormatter;

test('decimal', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);
    $formatter = new DefaultPriceFormatter(12345, $currency);

    expect($formatter->decimal())->toBe(123.45);
});

test('unit decimal', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);
    $formatter = new DefaultPriceFormatter(2000, $currency, 2);

    expect($formatter->unitDecimal())->toBe(10.0);
});

test('formatted', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);
    $formatter = new DefaultPriceFormatter(1000, $currency);

    $formatted = $formatter->formatted('en_US');

    expect($formatted)->toContain('10');
    expect($formatted)->toContain('$');
});

test('unit formatted', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);
    $formatter = new DefaultPriceFormatter(2000, $currency, 2);

    $formatted = $formatter->unitFormatted('en_US');

    expect($formatted)->toContain('10');
    expect($formatted)->toContain('$');
});

test('decimal no rounding', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 3,
    ]);
    $formatter = new DefaultPriceFormatter(12345, $currency);

    expect($formatter->decimal(false))->toBe(12.345);
});

test('uses default currency when none given', function () {
    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);
    $formatter = new DefaultPriceFormatter(5000);

    expect($formatter->decimal())->toBe(50.0);
});
