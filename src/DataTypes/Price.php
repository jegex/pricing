<?php

namespace Jegex\Pricing\DataTypes;

use Jegex\Pricing\Models\Contracts\Currency;
use Jegex\Pricing\Pricing\DefaultPriceFormatter;

class Price
{
    public function __construct(
        public int $value,
        public Currency $currency,
        public int $unitQty = 1
    ) {
        //
    }

    public function __get(string $name): mixed
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        return null;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    private function formatter(): DefaultPriceFormatter
    {
        return app(
            config('pricing.formatter', DefaultPriceFormatter::class),
            [
                'value' => $this->value,
                'currency' => $this->currency,
                'unitQty' => $this->unitQty,
            ]
        );
    }

    public function decimal(mixed ...$arguments): float
    {
        return $this->formatter()->decimal(...$arguments);
    }

    public function unitDecimal(mixed ...$arguments): float
    {
        return $this->formatter()->unitDecimal(...$arguments);
    }

    public function formatted(mixed ...$arguments): mixed
    {
        return $this->formatter()->formatted(...$arguments);
    }

    public function unitFormatted(mixed ...$arguments): mixed
    {
        return $this->formatter()->unitFormatted(...$arguments);
    }

    protected function formatValue(int|float $value, mixed ...$arguments): mixed
    {
        return $this->formatter()->formatValue($value, ...$arguments);
    }
}
