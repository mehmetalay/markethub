<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Data\ProviderResult;
use App\Domain\Marketplace\Models\MarketplaceAccount;

interface ChecksMarketplaceConnection
{
    public function checkConnection(MarketplaceAccount $account): ProviderResult;
}
