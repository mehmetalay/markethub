<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MetadataSyncRun;

interface SyncsMarketplaceMetadata
{
    public function syncCategories(MarketplaceAccount $account): MetadataSyncRun;

    public function syncBrands(MarketplaceAccount $account): MetadataSyncRun;

    public function syncAttributes(MarketplaceAccount $account): MetadataSyncRun;
}
