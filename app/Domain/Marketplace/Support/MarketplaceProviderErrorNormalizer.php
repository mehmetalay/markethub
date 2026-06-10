<?php

namespace App\Domain\Marketplace\Support;

use App\Domain\Marketplace\Contracts\ProviderErrorNormalizer;
use App\Domain\Marketplace\Data\ProviderError;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderException;
use Throwable;

class MarketplaceProviderErrorNormalizer implements ProviderErrorNormalizer
{
    public function normalize(Throwable $throwable): ProviderError
    {
        if ($throwable instanceof MarketplaceProviderException) {
            return new ProviderError(
                code: $throwable->errorCode(),
                message: $throwable->getMessage(),
            );
        }

        return new ProviderError(
            code: 'unexpected_provider_error',
            message: 'Marketplace provider operation failed.',
            category: 'unexpected',
        );
    }
}
