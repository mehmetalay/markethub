<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

use App\Domain\Marketplace\Contracts\ChecksMarketplaceConnection;
use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use App\Domain\Marketplace\Contracts\MarketplaceProvider;
use App\Domain\Marketplace\Contracts\SyncsMarketplaceMetadata;
use App\Domain\Marketplace\Data\ProviderRequestContext;
use App\Domain\Marketplace\Data\ProviderResult;
use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderCapabilityException;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MetadataSyncRun;
use Illuminate\Contracts\Container\Container;

class TrendyolProvider implements ChecksMarketplaceConnection, MarketplaceProvider, SyncsMarketplaceMetadata
{
    public function __construct(
        private readonly Container $container,
        private readonly TrendyolMetadataSyncService $metadataSyncService,
        private readonly TrendyolRateLimitGuard $rateLimitGuard,
    ) {}

    public function code(): MarketplaceCode
    {
        return MarketplaceCode::Trendyol;
    }

    public function name(): string
    {
        return 'Trendyol';
    }

    public function capabilities(): array
    {
        return [
            MarketplaceCapability::ConnectionCheck,
            MarketplaceCapability::MetadataSync,
            MarketplaceCapability::CatalogRead,
        ];
    }

    public function supports(MarketplaceCapability $capability): bool
    {
        return in_array($capability, $this->capabilities(), true);
    }

    public function ensureSupports(MarketplaceCapability $capability): void
    {
        if (! $this->supports($capability)) {
            throw new MarketplaceProviderCapabilityException($this->code(), $capability);
        }
    }

    public function checkConnection(MarketplaceAccount $account): ProviderResult
    {
        $client = $this->apiClient($account);
        $payload = $client->categories();

        return ProviderResult::successful(
            message: 'Trendyol bağlantısı başarıyla doğrulandı.',
            metadata: [
                'provider' => [
                    'code' => $this->code()->value,
                    'name' => $this->name(),
                ],
                'capability' => MarketplaceCapability::ConnectionCheck->value,
                'context' => ProviderRequestContext::fromAccount($account)->toSafeArray(),
                'category_count' => is_countable($payload['categories'] ?? null) ? count($payload['categories']) : null,
            ],
        );
    }

    public function syncCategories(MarketplaceAccount $account): MetadataSyncRun
    {
        $this->ensureSupports(MarketplaceCapability::MetadataSync);

        return $this->metadataSyncService->syncCategories($account);
    }

    public function syncBrands(MarketplaceAccount $account): MetadataSyncRun
    {
        $this->ensureSupports(MarketplaceCapability::MetadataSync);

        return $this->metadataSyncService->syncBrands($account);
    }

    public function syncAttributes(MarketplaceAccount $account): MetadataSyncRun
    {
        $this->ensureSupports(MarketplaceCapability::MetadataSync);

        return $this->metadataSyncService->syncAttributes($account);
    }

    private function apiClient(MarketplaceAccount $account): TrendyolApiClient
    {
        $account->loadMissing('marketplace');
        $context = ProviderRequestContext::fromAccount($account);

        return new TrendyolApiClient(
            httpClient: $this->container->make(MarketplaceHttpClient::class, [
                'context' => $context,
            ]),
            credentials: TrendyolCredentials::fromAccount($account),
            rateLimitGuard: $this->rateLimitGuard,
        );
    }
}
