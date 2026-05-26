<?php

namespace Jegex\Pricing\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Jegex\Pricing\Jobs\SyncPriceCurrencies;
use Jegex\Pricing\Models\Currency;
use Jegex\Pricing\Models\Price;

class PriceObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Price $price): void
    {
        if ($price->currency->default && $this->hasCurrenciesToSync($price)) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    public function updated(Price $price): void
    {
        if ($price->currency->default && $this->hasCurrenciesToSync($price)) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    public function deleted(Price $price): void
    {
        if ($price->currency->default && $this->hasCurrenciesToSync($price)) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    protected function hasCurrenciesToSync(Price $price): bool
    {
        return Currency::query()
            ->where('id', '!=', $price->currency_id)
            ->where('sync_prices', true)
            ->exists();
    }
}
