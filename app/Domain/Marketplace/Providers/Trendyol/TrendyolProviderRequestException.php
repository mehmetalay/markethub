<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

use App\Domain\Marketplace\Exceptions\MarketplaceProviderConnectionException;

class TrendyolProviderRequestException extends MarketplaceProviderConnectionException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'trendyol_provider_http_error';
    }
}
