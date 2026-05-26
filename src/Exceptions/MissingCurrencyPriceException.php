<?php

namespace Jegex\Pricing\Exceptions;

class MissingCurrencyPriceException extends PricingException
{
    public function __construct(
        ?string $currencyCode = null,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message ?: ($currencyCode
                ? "No price found for currency [{$currencyCode}]."
                : 'No price found for the requested currency.'),
            $code,
            $previous
        );
    }
}
