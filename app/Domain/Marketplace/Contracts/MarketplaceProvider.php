<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;

interface MarketplaceProvider
{
    public function code(): MarketplaceCode;

    public function name(): string;

    /**
     * @return list<MarketplaceCapability>
     */
    public function capabilities(): array;

    public function supports(MarketplaceCapability $capability): bool;

    public function ensureSupports(MarketplaceCapability $capability): void;
}
