<?php

namespace Tests\Unit;

use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use App\Domain\Marketplace\Data\ProviderRequestContext;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Http\LaravelMarketplaceHttpClient;
use Tests\TestCase;

class MarketplaceHttpClientBindingTest extends TestCase
{
    public function test_marketplace_http_client_contract_resolves_laravel_implementation(): void
    {
        $this->app->instance(ProviderRequestContext::class, new ProviderRequestContext(
            tenantId: 1,
            marketplaceAccountId: 10,
            marketplaceCode: MarketplaceCode::Trendyol,
            marketplaceName: 'Trendyol',
            accountName: 'Trendyol Ana Hesap',
        ));

        $client = $this->app->make(MarketplaceHttpClient::class);

        $this->assertInstanceOf(LaravelMarketplaceHttpClient::class, $client);
        $this->assertSame(MarketplaceCode::Trendyol, $client->context()->marketplaceCode);
    }
}
