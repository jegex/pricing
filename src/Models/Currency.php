<?php

namespace Jegex\Pricing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jegex\Pricing\Database\Factories\CurrencyFactory;
use Jegex\Pricing\Models\Contracts\Currency as CurrencyContract;
use Jegex\Pricing\Traits\HasDefaultRecord;
use Jegex\Pricing\Traits\HasMacros;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property float $exchange_rate
 * @property int $decimal_places
 * @property string $factor
 * @property bool $default
 * @property bool $enabled
 * @property bool $sync_prices
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> enabled(bool $enabled = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static> default(bool $default = true)
 * @method static \Illuminate\Database\Eloquent\Model|null getDefault()
 */
class Currency extends Model implements CurrencyContract
{
    /** @use HasFactory<\Jegex\Pricing\Database\Factories\CurrencyFactory> */
    use HasFactory;
    use HasDefaultRecord;
    use HasMacros;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'exchange_rate',
        'decimal_places',
        'enabled',
        'default',
        'sync_prices',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'default' => 'boolean',
            'sync_prices' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }

    protected static function newFactory(): CurrencyFactory
    {
        return CurrencyFactory::new();
    }

    public function scopeEnabled(Builder $query, bool $enabled = true): Builder
    {
        return $query->where('enabled', $enabled);
    }

    /** @return HasMany<\Jegex\Pricing\Models\Price, \Illuminate\Database\Eloquent\Model> */
    public function prices(): HasMany
    {
        /** @var HasMany<\Jegex\Pricing\Models\Price, \Illuminate\Database\Eloquent\Model> */
        return $this->hasMany(Price::class);
    }

    public function getFactorAttribute(): string
    {
        if ($this->decimal_places < 1) {
            return '1';
        }

        return sprintf("1%0{$this->decimal_places}d", 0);
    }
}
