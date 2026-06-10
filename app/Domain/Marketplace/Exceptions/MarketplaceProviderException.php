<?php

namespace App\Domain\Marketplace\Exceptions;

use RuntimeException;

class MarketplaceProviderException extends RuntimeException
{
    public function errorCode(): string
    {
        return 'marketplace_provider_error';
    }
}
