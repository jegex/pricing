<?php

namespace Jegex\Pricing\Contracts;

use Illuminate\Support\Collection;

interface Purchasable
{
    /** @return Collection<int, \Jegex\Pricing\Models\Price> */
    public function getPrices(): Collection;

    public function getUnitQuantity(): int;
}
