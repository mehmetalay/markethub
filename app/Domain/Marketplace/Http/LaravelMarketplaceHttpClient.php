<?php

namespace App\Domain\Marketplace\Http;

use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use App\Domain\Marketplace\Data\ProviderRequestContext;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

class LaravelMarketplaceHttpClient implements MarketplaceHttpClient
{
    public function __construct(
        private readonly Factory $http,
        private readonly ProviderRequestContext $context,
    ) {}

    public function context(): ProviderRequestContext
    {
        return $this->context;
    }

    public function pendingRequest(): PendingRequest
    {
        return $this->http
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'X-MarketHub-Tenant' => (string) $this->context->tenantId,
                'X-MarketHub-Marketplace' => $this->context->marketplaceCode->value,
            ]);
    }
}
