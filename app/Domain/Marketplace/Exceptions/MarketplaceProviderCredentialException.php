<?php

namespace App\Domain\Marketplace\Exceptions;

class MarketplaceProviderCredentialException extends MarketplaceProviderException
{
    public function errorCode(): string
    {
        return 'marketplace_provider_missing_credentials';
    }
}
