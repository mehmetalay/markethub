<?php

namespace App\Domain\Marketplace\Actions;

use App\Domain\Marketplace\Data\ProviderRequestContext;
use App\Domain\Marketplace\Data\ProviderResult;
use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceConnectionCheck;
use App\Domain\Marketplace\Support\MarketplaceProviderFactory;

class RecordMarketplaceConnectionCheck
{
    public function __construct(
        private readonly MarketplaceProviderFactory $providerFactory,
    ) {}

    public function execute(MarketplaceAccount $account): MarketplaceConnectionCheck
    {
        $account->loadMissing('marketplace');

        $provider = $this->providerFactory->forAccount($account);
        $provider->ensureSupports(MarketplaceCapability::ConnectionCheck);

        $context = ProviderRequestContext::fromAccount($account);
        $result = ProviderResult::pending(
            message: 'Bağlantı kontrolü altyapısı hazırlandı. Gerçek sağlayıcı kontrolü ilgili entegrasyon modülünde çalıştırılacaktır.',
            metadata: [
                'provider' => [
                    'code' => $provider->code()->value,
                    'name' => $provider->name(),
                ],
                'capability' => MarketplaceCapability::ConnectionCheck->value,
                'context' => $context->toSafeArray(),
            ],
        );

        return MarketplaceConnectionCheck::query()->create([
            'tenant_id' => $account->tenant_id,
            'marketplace_account_id' => $account->id,
            'status' => $result->status,
            'checked_at' => now(),
            'message' => $result->message,
            'metadata' => $result->metadata,
        ]);
    }
}
