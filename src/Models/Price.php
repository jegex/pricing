<?php

namespace Jegex\Pricing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jegex\Pricing\Casts\Price as CastsPrice;
use Jegex\Pricing\Database\Factories\PriceFactory;
use Jegex\Pricing\Models\Contracts\Price as PriceContract;
use Jegex\Pricing\Traits\HasMacros;

/**
 * @property int $id
 * @property int $currency_id
 * @property int|null $customer_group_id
 * @property int $min_quantity
 * @property int $priceable_id
 * @property string $priceable_type
 * @property \Jegex\Pricing\DataTypes\Price $price
 * @property \Jegex\Pricing\DataTypes\Price|null $compare_price
 * @property Currency|null $currency
 * @property \Illuminate\Database\Eloquent\Model $priceable
 */
class Price extends Model implements PriceContract
{
    /** @use HasFactory<\Jegex\Pricing\Database\Factories\PriceFactory> */
    use HasFactory;
    use HasMacros;

    protected $table = 'prices';

    protected $fillable = [
        'currency_id',
        'customer_group_id',
        'priceable_type',
        'priceable_id',
        'price',
        'compare_price',
        'min_quantity',
    ];

    protected $casts = [
        'price' => CastsPrice::class,
        'compare_price' => CastsPrice::class,
    ];

    protected static function newFactory(): PriceFactory
    {
        return PriceFactory::new();
    }

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
    public function priceable(): MorphTo
    {
        /** @var MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
        return $this->morphTo();
    }

    /** @return BelongsTo<Currency, \Illuminate\Database\Eloquent\Model> */
    public function currency(): BelongsTo
    {
        /** @var BelongsTo<Currency, \Illuminate\Database\Eloquent\Model> */
        return $this->belongsTo(Currency::class);
    }
}
