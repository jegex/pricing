<?php

namespace Jegex\Pricing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jegex\Pricing\Models\Currency;

/** @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jegex\Pricing\Models\Currency> */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'code' => $this->faker->unique()->currencyCode,
            'exchange_rate' => $this->faker->randomFloat(2, 0.1, 5),
            'decimal_places' => 2,
            'enabled' => true,
            'default' => true,
            'sync_prices' => false,
        ];
    }
}
