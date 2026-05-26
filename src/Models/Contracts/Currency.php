<?php

namespace Jegex\Pricing\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property float $exchange_rate
 * @property int $decimal_places
 * @property string $factor
 * @property bool $default
 * @property bool $enabled
 * @property bool $sync_prices
 */
interface Currency
{
    /**
     * @param Builder<\Illuminate\Database\Eloquent\Model> $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeEnabled(Builder $query, bool $enabled = true): Builder;

    /** @return HasMany<\Jegex\Pricing\Models\Price, \Illuminate\Database\Eloquent\Model> */
    public function prices(): HasMany;

    public function getFactorAttribute(): string;
}
