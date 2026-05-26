<?php

namespace Jegex\Pricing;

use Illuminate\Support\ServiceProvider;
use Jegex\Pricing\Contracts\PricingManagerInterface;
use Jegex\Pricing\Managers\PricingManager;
use Jegex\Pricing\Models\Price;
use Jegex\Pricing\Observers\PriceObserver;

class PricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pricing.php', 'pricing');

        $this->app->bind(PricingManagerInterface::class, function () {
            return $this->app->make(PricingManager::class);
        });
    }

    public function boot(): void
    {
        if (! config('pricing.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        Price::observe(PriceObserver::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pricing.php' => config_path('pricing.php'),
            ], 'pricing.config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'pricing.migrations');
        }
    }
}
