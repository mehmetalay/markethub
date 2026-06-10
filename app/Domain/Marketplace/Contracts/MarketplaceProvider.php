<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;

interface MarketplaceProvider
{
    public function code(): MarketplaceCode;

    /**
     * @return list<MarketplaceCapability>
     */
    public function capabilities(): array;
}
