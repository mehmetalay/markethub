<?php

namespace Tests\Unit;

use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderCapabilityException;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderNotFoundException;
use App\Domain\Marketplace\Support\ConfiguredMarketplaceProvider;
use App\Domain\Marketplace\Support\MarketplaceProviderFactory;
use App\Domain\Marketplace\Support\MarketplaceProviderRegistry;
use PHPUnit\Framework\TestCase;

class MarketplaceProviderCoreTest extends TestCase
{
    public function test_provider_can_be_resolved_by_marketplace_code(): void
    {
        $factory = new MarketplaceProviderFactory(MarketplaceProviderRegistry::withDefaultProviders());

        $provider = $factory->make(MarketplaceCode::TrendyolGo);

        $this->assertSame(MarketplaceCode::TrendyolGo, $provider->code());
        $this->assertTrue($provider->supports(MarketplaceCapability::ConnectionCheck));
    }

    public function test_unsupported_provider_code_throws_meaningful_exception(): void
    {
        $this->expectException(MarketplaceProviderNotFoundException::class);
        $this->expectExceptionMessage('amazon');

        (new MarketplaceProviderRegistry)->get(MarketplaceCode::Amazon);
    }

    public function test_provider_capability_check_can_fail(): void
    {
        $provider = new ConfiguredMarketplaceProvider(
            code: MarketplaceCode::Trendyol,
            name: 'Trendyol',
            capabilities: [
                MarketplaceCapability::ConnectionCheck,
            ],
        );

        $this->assertTrue($provider->supports(MarketplaceCapability::ConnectionCheck));
        $this->assertFalse($provider->supports(MarketplaceCapability::OrderRead));

        $this->expectException(MarketplaceProviderCapabilityException::class);

        $provider->ensureSupports(MarketplaceCapability::OrderRead);
    }
}
