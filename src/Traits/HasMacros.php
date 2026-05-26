<?php

namespace Jegex\Pricing\Traits;

use Illuminate\Support\Traits\Macroable;

trait HasMacros
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * @param array<int, mixed> $parameters
     */
    public function __call($method, $parameters): mixed
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * @param array<int, mixed> $parameters
     */
    public static function __callStatic($method, $parameters): mixed
    {
        return parent::__callStatic($method, $parameters);
    }
}
