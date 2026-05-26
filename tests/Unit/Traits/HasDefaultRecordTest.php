<?php

use Jegex\Pricing\Models\Currency;

beforeEach(function () {
    Currency::forgetDefaultCache();
});

test('get default returns null when none set', function () {

    Currency::factory()->create([
        'default' => false,
    ]);

    expect(Currency::getDefault())->toBeNull();
});

test('get default returns default record', function () {
    Currency::factory()->create([
        'code' => 'USD',
        'default' => true,
    ]);
    Currency::factory()->create([
        'code' => 'EUR',
        'default' => false,
    ]);

    $default = Currency::getDefault();

    expect($default)->not->toBeNull();
    expect($default->code)->toBe('USD');
});

test('get default is cached within request', function () {
    Currency::factory()->create([
        'code' => 'USD',
        'default' => true,
    ]);

    $first = Currency::getDefault();
    $second = Currency::getDefault();

    expect($first)->toBe($second);
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

    $result = Currency::query()->default(true)->get();

    expect($result)->toHaveCount(1);
});
