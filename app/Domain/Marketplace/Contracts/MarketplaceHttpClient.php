<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Data\ProviderRequestContext;

interface MarketplaceHttpClient
{
    public function context(): ProviderRequestContext;
}
