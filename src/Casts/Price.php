<?php

namespace Jegex\Pricing\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Jegex\Pricing\DataTypes\Price as PriceDataType;
use Jegex\Pricing\Models\Currency;

/** @implements CastsAttributes<\Jegex\Pricing\DataTypes\Price, mixed> */
class Price implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        /** @var \Jegex\Pricing\Models\Price $model */
        /** @var \Jegex\Pricing\Models\Contracts\Currency $currency */
        $currency = $model->currency ?: Currency::getDefault();

        $value = preg_replace('/[^0-9]/', '', $value);

        if (! is_numeric($value)) {
            throw new \InvalidArgumentException("Attribute [{$key}] must be numeric.");
        }

        return new PriceDataType(
            (int) $value,
            $currency,
        );
    }

    public function set($model, $key, $value, $attributes)
    {
        return [
            $key => $value instanceof PriceDataType ? $value->value : $value,
        ];
    }
}
