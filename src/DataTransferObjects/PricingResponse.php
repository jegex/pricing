<?php

namespace Jegex\Pricing\DataTransferObjects;

use Illuminate\Support\Collection;
use Jegex\Pricing\Models\Price;

class PricingResponse
{
    /**
     * @param Collection<int, Price> $priceBreaks
     * @param Collection<int, Price> $customerGroupPrices
     */
    public function __construct(
        public readonly Price $matched,
        public readonly Price $base,
        public readonly Collection $priceBreaks,
        public readonly Collection $customerGroupPrices,
    ) {
        //
    }
}
