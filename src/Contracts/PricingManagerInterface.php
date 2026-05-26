<?php

namespace Jegex\Pricing\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Jegex\Pricing\DataTransferObjects\PricingResponse;
use Jegex\Pricing\Models\Contracts\Currency;

interface PricingManagerInterface
{
    public function user(?Authenticatable $user): static;

    public function guest(): static;

    public function currency(?Currency $currency): static;

    public function qty(int $qty): static;

    /**
     * @param Collection<int, mixed>|null $customerGroups
     */
    public function customerGroups(?Collection $customerGroups): static;

    public function customerGroup(mixed $customerGroup): static;

    public function for(Purchasable $purchasable): static;

    public function get(): PricingResponse;
}
