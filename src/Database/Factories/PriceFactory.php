<?php

namespace Jegex\Pricing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

/** @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jegex\Pricing\Models\Price> */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(1, 2500),
            'compare_price' => $this->faker->numberBetween(1, 2500),
            'currency_id' => Currency::factory(),
            'min_quantity' => 1,
        ];
    }
}
