<?php

namespace Tests\Feature;

use App\Domain\Marketplace\Actions\RecordMarketplaceConnectionCheck;
use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Enums\MarketplaceConnectionStatus;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderCredentialException;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceAttribute;
use App\Domain\Marketplace\Models\MarketplaceAttributeValue;
use App\Domain\Marketplace\Models\MarketplaceBrand;
use App\Domain\Marketplace\Models\MarketplaceCategory;
use App\Domain\Marketplace\Models\MetadataSyncRun;
use App\Domain\Marketplace\Providers\Trendyol\TrendyolProvider;
use App\Domain\Marketplace\Support\MarketplaceProviderFactory;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrendyolMetadataProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_trendyol_provider_can_be_resolved_from_registry(): void
    {
        $provider = app(MarketplaceProviderFactory::class)->make(MarketplaceCode::Trendyol);

        $this->assertInstanceOf(TrendyolProvider::class, $provider);
        $this->assertSame(MarketplaceCode::Trendyol, $provider->code());
    }

    public function test_missing_credentials_throw_provider_exception(): void
    {
        $account = $this->account([
            'api_key' => 'api-key',
            'api_secret' => 'api-secret',
        ]);

        $this->expectException(MarketplaceProviderCredentialException::class);
        $this->expectExceptionMessage('Trendyol API bilgileri eksik');

        app(MarketplaceProviderFactory::class)
            ->forAccount($account)
            ->checkConnection($account);
    }

    public function test_connection_check_uses_real_trendyol_endpoint_shape_without_leaking_credentials(): void
    {
        Http::fake([
            'https://apigw.trendyol.com/integration/product/product-categories' => Http::response([
                'categories' => [
                    ['id' => 100, 'name' => 'Elektronik'],
                ],
            ]),
        ]);

        $account = $this->account();

        $check = app(RecordMarketplaceConnectionCheck::class)->execute($account);

        $this->assertSame(MarketplaceConnectionStatus::Successful, $check->status);
        $this->assertSame('Trendyol bağlantısı başarıyla doğrulandı.', $check->message);
        $this->assertSame('trendyol', $check->metadata['provider']['code']);

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && $request->url() === 'https://apigw.trendyol.com/integration/product/product-categories'
            && $request->header('Authorization')[0] === 'Basic '.base64_encode('api-key:api-secret')
            && $request->header('User-Agent')[0] === '123456 - MarketHub'
            && $request->header('X-MarketHub-Supplier') === []);

        $encoded = json_encode($check->metadata, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('api-key', $encoded);
        $this->assertStringNotContainsString('api-secret', $encoded);
    }

    public function test_category_metadata_is_normalized_and_persisted(): void
    {
        Http::fake([
            'https://apigw.trendyol.com/integration/product/product-categories' => Http::response([
                'categories' => [
                    [
                        'id' => 100,
                        'name' => 'Elektronik',
                        'subCategories' => [
                            ['id' => 101, 'name' => 'Telefon'],
                        ],
                    ],
                ],
            ]),
        ]);

        $run = app(MarketplaceProviderFactory::class)
            ->forAccount($account = $this->account())
            ->syncCategories($account);

        $this->assertSame(MetadataSyncStatus::Succeeded, $run->status);
        $this->assertSame(2, MarketplaceCategory::query()->count());
        $this->assertDatabaseHas('marketplace_categories', [
            'marketplace_id' => $account->marketplace_id,
            'external_id' => '101',
            'parent_external_id' => '100',
            'name' => 'Telefon',
            'path' => 'Elektronik / Telefon',
        ]);
        $this->assertSame(2, $run->items()->count());

        app(MarketplaceProviderFactory::class)->forAccount($account)->syncCategories($account);

        $this->assertSame(2, MarketplaceCategory::query()->count());
    }

    public function test_brand_metadata_is_normalized_and_persisted(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'page=0')) {
                return Http::response([
                    'brands' => [
                        ['id' => 10, 'name' => 'Marka A'],
                        ['id' => 11, 'name' => 'Marka B'],
                    ],
                    'totalPages' => 1,
                ]);
            }

            return Http::response(['brands' => [], 'totalPages' => 1]);
        });

        $run = app(MarketplaceProviderFactory::class)
            ->forAccount($account = $this->account())
            ->syncBrands($account);

        $this->assertSame(MetadataSyncStatus::Succeeded, $run->status);
        $this->assertSame(2, MarketplaceBrand::query()->count());
        $this->assertDatabaseHas('marketplace_brands', [
            'marketplace_id' => $account->marketplace_id,
            'external_id' => '10',
            'name' => 'Marka A',
        ]);
        $this->assertSame(2, $run->items()->count());
    }

    public function test_attribute_metadata_and_values_are_normalized_and_persisted(): void
    {
        $account = $this->account();

        MarketplaceCategory::query()->create([
            'marketplace_id' => $account->marketplace_id,
            'external_id' => '101',
            'name' => 'Telefon',
        ]);

        Http::fake([
            'https://apigw.trendyol.com/integration/product/product-categories/101/attributes' => Http::response([
                'categoryAttributes' => [
                    [
                        'attribute' => ['id' => 500, 'name' => 'Renk'],
                        'required' => true,
                        'attributeValues' => [
                            ['id' => 1, 'name' => 'Siyah'],
                            ['id' => 2, 'name' => 'Beyaz'],
                        ],
                    ],
                ],
            ]),
        ]);

        $run = app(MarketplaceProviderFactory::class)
            ->forAccount($account)
            ->syncAttributes($account);

        $attribute = MarketplaceAttribute::query()->firstOrFail();

        $this->assertSame(MetadataSyncStatus::Succeeded, $run->status);
        $this->assertSame('500', $attribute->external_id);
        $this->assertSame('Renk', $attribute->name);
        $this->assertTrue($attribute->is_required);
        $this->assertSame(2, MarketplaceAttributeValue::query()->count());
        $this->assertDatabaseHas('marketplace_attribute_values', [
            'marketplace_id' => $account->marketplace_id,
            'marketplace_attribute_id' => $attribute->id,
            'external_id' => '1',
            'value' => 'Siyah',
        ]);
        $this->assertSame(3, $run->items()->count());
    }

    public function test_failed_http_response_creates_failed_sync_run(): void
    {
        Http::fake([
            'https://apigw.trendyol.com/integration/product/product-categories' => Http::response([
                'message' => 'Unauthorized request',
            ], 401),
        ]);

        $run = app(MarketplaceProviderFactory::class)
            ->forAccount($account = $this->account())
            ->syncCategories($account);

        $this->assertSame(MetadataSyncStatus::Failed, $run->status);
        $this->assertSame(1, MetadataSyncRun::query()->count());
        $this->assertSame(1, $run->items()->where('status', MetadataSyncStatus::Failed)->count());
        $this->assertStringContainsString('HTTP 401', $run->message);
        $this->assertStringNotContainsString('api-key', $run->message);
        $this->assertStringNotContainsString('api-secret', $run->message);
    }

    /**
     * @param  array<string, mixed>|null  $credentials
     */
    private function account(?array $credentials = null): MarketplaceAccount
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-'.str()->random(8),
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);

        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        return MarketplaceAccount::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_id' => $marketplace->id,
            'name' => 'Trendyol Ana Hesap',
            'status' => MarketplaceAccountStatus::Active,
            'credentials' => $credentials ?? [
                'supplier_id' => '123456',
                'api_key' => 'api-key',
                'api_secret' => 'api-secret',
            ],
        ]);
    }
}
