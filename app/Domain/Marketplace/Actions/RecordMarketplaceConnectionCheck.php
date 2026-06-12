<?php

namespace App\Domain\Marketplace\Actions;

use App\Domain\Marketplace\Contracts\ChecksMarketplaceConnection;
use App\Domain\Marketplace\Data\ProviderRequestContext;
use App\Domain\Marketplace\Data\ProviderResult;
use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceConnectionCheck;
use App\Domain\Marketplace\Support\MarketplaceProviderErrorNormalizer;
use App\Domain\Marketplace\Support\MarketplaceProviderFactory;
use Throwable;

class RecordMarketplaceConnectionCheck
{
    public function __construct(
        private readonly MarketplaceProviderFactory $providerFactory,
        private readonly MarketplaceProviderErrorNormalizer $errorNormalizer,
    ) {}

    public function execute(MarketplaceAccount $account): MarketplaceConnectionCheck
    {
        $account->loadMissing('marketplace');

        $provider = $this->providerFactory->forAccount($account);
        $provider->ensureSupports(MarketplaceCapability::ConnectionCheck);

        $context = ProviderRequestContext::fromAccount($account);

        try {
            $result = $provider instanceof ChecksMarketplaceConnection
                ? $provider->checkConnection($account)
                : ProviderResult::pending(
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
        } catch (Throwable $throwable) {
            $result = ProviderResult::failed($this->errorNormalizer->normalize($throwable));
        }

        return MarketplaceConnectionCheck::query()->create([
            'tenant_id' => $account->tenant_id,
            'marketplace_account_id' => $account->id,
            'status' => $result->status,
            'checked_at' => now(),
            'message' => $result->message,
            'metadata' => [
                ...$result->metadata,
                'error' => $result->error?->toArray(),
            ],
        ]);
    }
}
