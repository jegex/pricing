<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('prices_inc_tax')) {
    function prices_inc_tax(): bool
    {
        return (bool) config('pricing.stored_inclusive_of_tax', false);
    }
}

if (! function_exists('can_drop_foreign_keys')) {
    function can_drop_foreign_keys(): bool
    {
        return DB::getDriverName() !== 'sqlite' || version_compare(app()->version(), '11.15.0', '>=');
    }
}
