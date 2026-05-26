<?php

namespace Jegex\Pricing\Actions;

use Illuminate\Support\Facades\DB;
use Jegex\Pricing\Models\Currency;

class CreateCurrencyPrices
{
    public function handle(Currency $incomingCurrency, Currency $baseCurrency): void
    {
        $now = now();

        $basePrices = DB::table('prices')
            ->selectRaw('ROUND(price * ?) as price', [$incomingCurrency->exchange_rate])
            ->selectRaw('ROUND(compare_price * ?) as compare_price', [$incomingCurrency->exchange_rate])
            ->selectRaw('priceable_type')
            ->selectRaw('customer_group_id')
            ->selectRaw('min_quantity')
            ->selectRaw('priceable_id')
            ->selectRaw('? as currency_id', [$incomingCurrency->id])
            ->selectRaw('? as created_at', [$now])
            ->selectRaw('? as updated_at', [$now])
            ->where('currency_id', $baseCurrency->id);

        DB::table('prices')->insertUsing([
            'price',
            'compare_price',
            'priceable_type',
            'customer_group_id',
            'min_quantity',
            'priceable_id',
            'currency_id',
            'created_at',
            'updated_at',
        ], $basePrices);
    }
}
