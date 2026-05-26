<?php

use Illuminate\Support\Collection;
use Jegex\Pricing\Contracts\Purchasable;
use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Managers\PricingManager;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

test('pipeline can modify pricing response', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $purchasable = new class($currency->id) implements Purchasable
    {
        public function __construct(private int $currencyId) {}

        public function getPrices(): Collection
        {
            $price = new Price;
            $price->price = new PriceDataType(2000, Currency::getDefault());
            $price->currency_id = $this->currencyId;
            $price->min_quantity = 1;

            return collect([$price]);
        }

        public function getUnitQuantity(): int
        {
            return 1;
        }
    };

    $this->app['config']->set('pricing.pipelines', [
        function ($pricingManager, $next) {
            $pricingManager->pricing->matched->price = new PriceDataType(1000, Currency::getDefault());

            return $next($pricingManager);
        },
    ]);

    $manager = new PricingManager;
    $response = $manager->currency($currency)->for($purchasable)->get();

    expect($response->matched->price->value)->toBe(1000);
});

test('multiple pipelines run in sequence', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $purchasable = new class($currency->id) implements Purchasable
    {
        public function __construct(private int $currencyId) {}

        public function getPrices(): Collection
        {
            $price = new Price;
            $price->price = new PriceDataType(3000, Currency::getDefault());
            $price->currency_id = $this->currencyId;
            $price->min_quantity = 1;

            return collect([$price]);
        }

        public function getUnitQuantity(): int
        {
            return 1;
        }
    };

    $this->app['config']->set('pricing.pipelines', [
        function ($pricingManager, $next) {
            $pricingManager->pricing->matched->price = new PriceDataType(
                $pricingManager->pricing->matched->price->value + 1000,
                Currency::getDefault()
            );

            return $next($pricingManager);
        },
        function ($pricingManager, $next) {
            $pricingManager->pricing->matched->price = new PriceDataType(
                $pricingManager->pricing->matched->price->value * 2,
                Currency::getDefault()
            );

            return $next($pricingManager);
        },
    ]);

    $manager = new PricingManager;
    $response = $manager->currency($currency)->for($purchasable)->get();

    expect($response->matched->price->value)->toBe(8000);
});
