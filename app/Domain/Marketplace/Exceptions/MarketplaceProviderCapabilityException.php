<?php

namespace App\Domain\Marketplace\Exceptions;

use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;

class MarketplaceProviderCapabilityException extends MarketplaceProviderException
{
    public function __construct(MarketplaceCode $code, MarketplaceCapability $capability)
    {
        parent::__construct("Marketplace provider [{$code->value}] does not support capability [{$capability->value}].");
    }

    public function errorCode(): string
    {
        return 'marketplace_provider_capability_not_supported';
    }
}
