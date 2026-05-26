<?php

namespace Jegex\Pricing\Facades;

use Illuminate\Support\Facades\Facade;
use Jegex\Pricing\Contracts\PricingManagerInterface;
use Jegex\Pricing\Managers\PricingManager;

/**
 * @method static PricingManager for(\Jegex\Pricing\Contracts\Purchasable $purchasable)
 * @method static PricingManager user(\Illuminate\Contracts\Auth\Authenticatable|null $user)
 * @method static PricingManager guest()
 * @method static PricingManager currency(\Jegex\Pricing\Models\Contracts\Currency|null $currency)
 * @method static PricingManager qty(int $qty)
 * @method static PricingManager customerGroups(\Illuminate\Support\Collection<int, mixed>|null $customerGroups)
 * @method static PricingManager customerGroup($customerGroup)
 * @method static \Jegex\Pricing\DataTransferObjects\PricingResponse get()
 *
 * @see PricingManager
 */
class Pricing extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PricingManagerInterface::class;
    }
}
