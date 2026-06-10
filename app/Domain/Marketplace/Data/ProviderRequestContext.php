<?php

namespace App\Domain\Marketplace\Data;

use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Models\MarketplaceAccount;

final readonly class ProviderRequestContext
{
    public function __construct(
        public int $tenantId,
        public int $marketplaceAccountId,
        public MarketplaceCode $marketplaceCode,
        public string $marketplaceName,
        public string $accountName,
    ) {}

    public static function fromAccount(MarketplaceAccount $account): self
    {
        $account->loadMissing('marketplace');

        return new self(
            tenantId: $account->tenant_id,
            marketplaceAccountId: $account->id,
            marketplaceCode: $account->marketplace->code,
            marketplaceName: $account->marketplace->name,
            accountName: $account->name,
        );
    }

    /**
     * @return array{tenant_id: int, marketplace_account_id: int, marketplace_code: string, marketplace_name: string, account_name: string}
     */
    public function toSafeArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'marketplace_account_id' => $this->marketplaceAccountId,
            'marketplace_code' => $this->marketplaceCode->value,
            'marketplace_name' => $this->marketplaceName,
            'account_name' => $this->accountName,
        ];
    }
}
