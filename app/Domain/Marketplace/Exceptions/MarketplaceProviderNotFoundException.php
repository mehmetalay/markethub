<?php

namespace App\Domain\Marketplace\Exceptions;

use App\Domain\Marketplace\Enums\MarketplaceCode;

class MarketplaceProviderNotFoundException extends MarketplaceProviderException
{
    public function __construct(MarketplaceCode $code)
    {
        parent::__construct("Marketplace provider is not registered for code [{$code->value}].");
    }

    public function errorCode(): string
    {
        return 'marketplace_provider_not_found';
    }
}
