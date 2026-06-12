<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Data\ProviderRequestContext;
use Illuminate\Http\Client\PendingRequest;

interface MarketplaceHttpClient
{
    public function context(): ProviderRequestContext;

    public function pendingRequest(): PendingRequest;
}
