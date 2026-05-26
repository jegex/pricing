<?php

use Illuminate\Support\Collection;
use Jegex\Pricing\Contracts\Purchasable;
use Jegex\Pricing\DataTransferObjects\PricingResponse;
use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Exceptions\MissingCurrencyPriceException;
use Jegex\Pricing\Managers\PricingManager;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

function makePurchasable(int $priceValue, int $unitQty = 1, ?int $currencyId = null, ?int $customerGroupId = null, int $minQuantity = 1): Purchasable
{
    return new class($priceValue, $unitQty, $currencyId, $customerGroupId, $minQuantity) implements Purchasable
    {
        public function __construct(
            private int $priceValue,
            private int $unitQty,
            private ?int $currencyId,
            private ?int $customerGroupId,
            private int $minQuantity,
        ) {}

        public function getPrices(): Collection
        {
            $price = new Price;
            $price->price = new PriceDataType($this->priceValue, Currency::getDefault());
            $price->currency_id = $this->currencyId ?? Currency::getDefault()->id;
            $price->customer_group_id = $this->customerGroupId;
            $price->min_quantity = $this->minQuantity;

            return collect([$price]);
        }

        public function getUnitQuantity(): int
        {
            return $this->unitQty;
        }
    };
}

test('can get price for purchasable', function () {
    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $purchasable = makePurchasable(1000, 1, $currency->id);

    $manager = new PricingManager;
    $response = $manager->currency($currency)->for($purchasable)->get();

    expect($response)->toBeInstanceOf(PricingResponse::class);
    expect($response->matched->price->value)->toBe(1000);
});

test('throws exception when no purchasable set', function () {
    $this->expectException(\Jegex\Pricing\Exceptions\PricingException::class);
    $this->expectExceptionMessage('No purchasable set.');

    $manager = new PricingManager;
    $manager->get();
});

test('throws exception when no currency price exists', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'default' => true,
    ]);
    $otherCurrency = Currency::factory()->create([
        'code' => 'EUR',
        'default' => false,
    ]);

    $purchasable = makePurchasable(1000, 1, $currency->id);

    $this->expectException(MissingCurrencyPriceException::class);

    $manager = new PricingManager;
    $manager->currency($otherCurrency)->for($purchasable)->get();
});

test('customer group price is preferred', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $purchasable = new class($currency->id) implements Purchasable
    {
        public function __construct(private int $currencyId) {}

        public function getPrices(): Collection
        {
            $currency = Currency::getDefault();

            $basePrice = new Price;
            $basePrice->price = new PriceDataType(2000, $currency);
            $basePrice->currency_id = $this->currencyId;
            $basePrice->customer_group_id = null;
            $basePrice->min_quantity = 1;

            $groupPrice = new Price;
            $groupPrice->price = new PriceDataType(1500, $currency);
            $groupPrice->currency_id = $this->currencyId;
            $groupPrice->customer_group_id = 1;
            $groupPrice->min_quantity = 1;

            return collect([$basePrice, $groupPrice]);
        }

        public function getUnitQuantity(): int
        {
            return 1;
        }
    };

    $manager = new PricingManager;
    $response = $manager->currency($currency)
        ->customerGroup((object) ['id' => 1])
        ->for($purchasable)
        ->get();

    expect($response->matched->price->value)->toBe(1500);
});

test('price break is preferred for high quantities', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $purchasable = new class($currency->id) implements Purchasable
    {
        public function __construct(private int $currencyId) {}

        public function getPrices(): Collection
        {
            $currency = Currency::getDefault();

            $basePrice = new Price;
            $basePrice->price = new PriceDataType(2000, $currency);
            $basePrice->currency_id = $this->currencyId;
            $basePrice->customer_group_id = null;
            $basePrice->min_quantity = 1;

            $bulkPrice = new Price;
            $bulkPrice->price = new PriceDataType(1500, $currency);
            $bulkPrice->currency_id = $this->currencyId;
            $bulkPrice->customer_group_id = null;
            $bulkPrice->min_quantity = 10;

            return collect([$basePrice, $bulkPrice]);
        }

        public function getUnitQuantity(): int
        {
            return 1;
        }
    };

    $manager = new PricingManager;
    $response = $manager->currency($currency)
        ->qty(10)
        ->for($purchasable)
        ->get();

    expect($response->matched->price->value)->toBe(1500);
});
