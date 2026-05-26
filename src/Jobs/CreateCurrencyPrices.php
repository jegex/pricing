<?php

namespace Jegex\Pricing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jegex\Pricing\Actions\CreateCurrencyPrices as CreateCurrencyPricesAction;
use Jegex\Pricing\Models\Currency;

class CreateCurrencyPrices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $tries = 1;

    public function __construct(public Currency $currency) {}

    public function handle(): void
    {
        $default = Currency::where('default', true)->first();

        if (! $default) {
            return;
        }

        if ($default->id == $this->currency->id) {
            $default = Currency::whereBetween(
                'updated_at',
                [now()->subSeconds(15), now()]
            )->whereDefault(false)->first();

            if (! $default) {
                return;
            }
        }

        (new CreateCurrencyPricesAction)->handle($this->currency, $default);
    }
}
