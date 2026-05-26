<?php

namespace Jegex\Pricing\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jegex\Pricing\Models\Currency;

/**
 * @property int $id
 * @property int $currency_id
 * @property int|null $customer_group_id
 * @property int $min_quantity
 * @property int $priceable_id
 * @property string $priceable_type
 * @property \Jegex\Pricing\DataTypes\Price $price
 * @property \Jegex\Pricing\DataTypes\Price|null $compare_price
 * @property Currency $currency
 */
interface Price
{
    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
    public function priceable(): MorphTo;

    /** @return BelongsTo<Currency, \Illuminate\Database\Eloquent\Model> */
    public function currency(): BelongsTo;
}
