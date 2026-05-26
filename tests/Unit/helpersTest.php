<?php

test('prices inc tax defaults to false', function () {
    expect(prices_inc_tax())->toBeFalse();
});

test('prices inc tax reads from config', function () {
    $this->app['config']->set('pricing.stored_inclusive_of_tax', true);

    expect(prices_inc_tax())->toBeTrue();
});

test('can drop foreign keys on sqlite', function () {
    $result = can_drop_foreign_keys();

    expect($result)->toBeBool();
});
