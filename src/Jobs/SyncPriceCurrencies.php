<?php

namespace Jegex\Pricing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

class SyncPriceCurrencies implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $tries = 1;

    public function uniqueId(): string
    {
        return 'sync_price_'.$this->price->id;
    }

    public function __construct(protected Price $price) {}

    public function handle(): void
    {
        $currencies = Currency::where('id', '!=', $this->price->currency_id)
            ->where('sync_prices', true)
            ->get();

        foreach ($currencies as $currency) {
            $priceCounterpart = Price::where('priceable_id', $this->price->priceable_id)
                ->where('priceable_type', $this->price->priceable_type)
                ->where('currency_id', $currency->id)
                ->where('id', '!=', $this->price->id)
                ->where('min_quantity', $this->price->min_quantity)
                ->where('customer_group_id', $this->price->customer_group_id)
                ->first();

            if (! $priceCounterpart) {
                $priceCounterpart = (new Price)->forceFill([
                    ...Arr::except($this->price->getAttributes(), ['id']),
                    'currency_id' => $currency->id,
                    'price' => (int) round($this->price->price->value * $currency->exchange_rate),
                    'compare_price' => $this->price->compare_price
                        ? (int) round($this->price->compare_price->value * $currency->exchange_rate)
                        : null,
                ]);

                $priceCounterpart->saveQuietly();

                continue;
            }

            $priceCounterpart->forceFill([
                'price' => (int) round($this->price->price->value * $currency->exchange_rate),
                'compare_price' => $this->price->compare_price
                    ? (int) round($this->price->compare_price->value * $currency->exchange_rate)
                    : null,
            ]);
            $priceCounterpart->saveQuietly();
        }
    }
}
