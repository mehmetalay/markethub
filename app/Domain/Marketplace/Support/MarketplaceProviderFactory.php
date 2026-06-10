<?php

namespace App\Domain\Marketplace\Support;

use App\Domain\Marketplace\Contracts\MarketplaceProvider;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Models\MarketplaceAccount;

class MarketplaceProviderFactory
{
    public function __construct(
        private readonly MarketplaceProviderRegistry $registry,
    ) {}

    public function make(MarketplaceCode|string $code): MarketplaceProvider
    {
        if (is_string($code)) {
            $code = MarketplaceCode::from($code);
        }

        return $this->registry->get($code);
    }

    public function forAccount(MarketplaceAccount $account): MarketplaceProvider
    {
        $account->loadMissing('marketplace');

        return $this->make($account->marketplace->code);
    }
}
