<?php

namespace App\Domain\Marketplace\Exceptions;

class MarketplaceProviderConnectionException extends MarketplaceProviderException
{
    public function errorCode(): string
    {
        return 'marketplace_provider_connection_error';
    }
}
