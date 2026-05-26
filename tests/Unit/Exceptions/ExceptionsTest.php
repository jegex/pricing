<?php

use Jegex\Pricing\Exceptions\MissingCurrencyPriceException;
use Jegex\Pricing\Exceptions\PricingException;

test('pricing exception extends exception', function () {
    $e = new PricingException('test');

    expect($e)->toBeInstanceOf(Exception::class);
    expect($e->getMessage())->toBe('test');
});

test('missing currency price exception', function () {
    $e = new MissingCurrencyPriceException('USD');

    expect($e)->toBeInstanceOf(PricingException::class);
    expect($e->getMessage())->toBe('No price found for currency [USD].');
});

test('missing currency price exception with custom message', function () {
    $e = new MissingCurrencyPriceException(null, 'Custom message');

    expect($e)->toBeInstanceOf(PricingException::class);
    expect($e->getMessage())->toBe('Custom message');
});

test('missing currency price exception default message', function () {
    $e = new MissingCurrencyPriceException;

    expect($e)->toBeInstanceOf(PricingException::class);
    expect($e->getMessage())->toBe('No price found for the requested currency.');
});
