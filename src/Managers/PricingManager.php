<?php

namespace Jegex\Pricing\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Jegex\Pricing\Contracts\PricingManagerInterface;
use Jegex\Pricing\Contracts\Purchasable;
use Jegex\Pricing\DataTransferObjects\PricingResponse;
use Jegex\Pricing\Exceptions\MissingCurrencyPriceException;
use Jegex\Pricing\Exceptions\PricingException;
use Jegex\Pricing\Models\Contracts\Currency as CurrencyContract;
use Jegex\Pricing\Models\Currency;

class PricingManager implements PricingManagerInterface
{
    public PricingResponse $pricing;

    public ?Purchasable $purchasable = null;

    public ?Authenticatable $user = null;

    public ?CurrencyContract $currency = null;

    public int $qty = 1;

    /** @var Collection<int, mixed>|null */
    public ?Collection $customerGroups = null;

    public function for(Purchasable $purchasable): static
    {
        $this->purchasable = $purchasable;

        return $this;
    }

    public function user(?Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function guest(): static
    {
        $this->user = null;

        return $this;
    }

    public function currency(?CurrencyContract $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function qty(int $qty): static
    {
        $this->qty = max(1, $qty);

        return $this;
    }

    /**
     * @param Collection<int, mixed>|null $customerGroups
     */
    public function customerGroups(?Collection $customerGroups): static
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    public function customerGroup(mixed $customerGroup): static
    {
        $this->customerGroups(
            collect([$customerGroup])
        );

        return $this;
    }

    public function get(): PricingResponse
    {
        if (! $this->purchasable) {
            throw new PricingException('No purchasable set.');
        }

        if (! $this->currency) {
            /** @var CurrencyContract $defaultCurrency */
            $defaultCurrency = Currency::getDefault();
            $this->currency = $defaultCurrency;
        }

        if (! $this->customerGroups || ! $this->customerGroups->count()) {
            $this->customerGroups = collect();
        }

        /** @var Currency $currency */
        $currency = $this->currency;

        $currencyPrices = $this->purchasable->getPrices()->filter(function ($price) use ($currency) {
            return $price->currency_id == $currency->id;
        });

        if (! $currencyPrices->count()) {
            throw new MissingCurrencyPriceException($currency->code);
        }

        $prices = $currencyPrices->filter(function ($price) {
            return ! $price->customer_group_id ||
                $this->customerGroups->pluck('id')->contains($price->customer_group_id);
        })->sortBy('price');

        $basePrice = $prices->first(fn ($price) => $price->min_quantity == 1 && ! $price->customer_group_id);

        $matched = $basePrice;

        $potentialGroupPrice = $prices->filter(function ($price) {
            return (bool) $price->customer_group_id && ($price->min_quantity == 1);
        })->sortBy('price');

        $matched = $potentialGroupPrice->first() ?: $matched;

        $priceBreaks = $prices->filter(function ($price) {
            return $price->min_quantity > 1 && $this->qty >= $price->min_quantity;
        })->sortBy('price');

        $matched = $priceBreaks->first() ?: $matched;

        if (! $matched) {
            throw new PricingException('No price set.');
        }

        $this->pricing = new PricingResponse(
            matched: $matched,
            base: $prices->first(fn ($price) => $price->min_quantity == 1),
            priceBreaks: $prices->filter(fn ($price) => $price->min_quantity > 1),
            customerGroupPrices: $prices->filter(fn ($price) => (bool) $price->customer_group_id)
        );

        $response = app(Pipeline::class)
            ->send($this)
            ->through(
                config('pricing.pipelines', [])
            )->then(fn ($pricingManager) => $pricingManager->pricing);

        $this->reset();

        return $response;
    }

    private function reset(): void
    {
        $this->purchasable = null;
        $this->user = null;
        $this->currency = null;
        $this->qty = 1;
        $this->customerGroups = null;
    }
}
