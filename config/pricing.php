<?php

use Jegex\Pricing\Pricing\DefaultPriceFormatter;

return [

    'stored_inclusive_of_tax' => env('PRICING_STORE_INCLUSIVE_OF_TAX', false),

    'formatter' => DefaultPriceFormatter::class,

    'pipelines' => [],

    'database' => [
        'disable_migrations' => env('PRICING_DISABLE_MIGRATIONS', false),
    ],

];
