<?php

namespace Tests\Feature;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Enums\ProductVariantStatus;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductPrice;
use App\Domain\Catalog\Models\ProductStock;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Listing\Enums\ListingPayloadType;
use App\Domain\Listing\Enums\ListingStatus;
use App\Domain\Listing\Enums\ListingVariantStatus;
use App\Domain\Listing\Models\Listing;
use App\Domain\Listing\Models\ListingPayload;
use App\Domain\Listing\Models\ListingStatusHistory;
use App\Domain\Listing\Models\ListingVariant;
use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListingFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_listing_page(): void
    {
        $this->get('/listings')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_listing_list(): void
    {
        $user = $this->userForTenant($this->tenant('tenant-one'));

        $this->actingAs($user)
            ->withHeaders($this->inertiaHeaders())
            ->get('/listings')
            ->assertOk();
    }

    public function test_listing_can_be_created_for_authenticated_users_tenant_without_http_call(): void
    {
        Http::preventStrayRequests();

        $tenant = $this->tenant('tenant-one');
        $product = $this->productWithVariants($tenant, 'Test Ürünü');
        $account = $this->marketplaceAccount($tenant);

        $this->actingAs($this->userForTenant($tenant))
            ->post('/listings', [
                'product_id' => $product->id,
                'marketplace_account_id' => $account->id,
            ])
            ->assertRedirect();

        $listing = Listing::query()->firstOrFail();

        $this->assertSame($tenant->id, $listing->tenant_id);
        $this->assertSame($product->id, $listing->product_id);
        $this->assertSame($account->id, $listing->marketplace_account_id);
        $this->assertSame($account->marketplace_id, $listing->marketplace_id);
        $this->assertSame(ListingStatus::Draft, $listing->status);
        $this->assertSame(2, ListingVariant::query()->where('listing_id', $listing->id)->count());

        $variant = ListingVariant::query()->where('sku', 'SKU-1-'.$tenant->id)->firstOrFail();
        $this->assertSame($tenant->id, $variant->tenant_id);
        $this->assertSame(ListingVariantStatus::Draft, $variant->status);
    }

    public function test_listings_are_limited_to_authenticated_users_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $visibleListing = $this->listing($tenant, 'Görünen Ürün');
        $this->listing($otherTenant, 'Başka Tenant Ürünü');

        $response = $this->actingAs($this->userForTenant($tenant))
            ->withHeaders($this->inertiaHeaders())
            ->get('/listings')
            ->assertOk();

        $listings = $response->json('props.listings');

        $this->assertCount(1, $listings);
        $this->assertSame($visibleListing->id, $listings[0]['id']);
        $this->assertStringNotContainsString('Başka Tenant Ürünü', $response->getContent());
    }

    public function test_other_tenant_product_cannot_be_used_for_listing(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $product = $this->productWithVariants($otherTenant, 'Başka Tenant Ürünü');
        $account = $this->marketplaceAccount($tenant);

        $this->actingAs($this->userForTenant($tenant))
            ->from('/listings/create')
            ->post('/listings', [
                'product_id' => $product->id,
                'marketplace_account_id' => $account->id,
            ])
            ->assertRedirect('/listings/create')
            ->assertSessionHasErrors('product_id');

        $this->assertSame(0, Listing::query()->count());
    }

    public function test_other_tenant_marketplace_account_cannot_be_used_for_listing(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $product = $this->productWithVariants($tenant, 'Test Ürünü');
        $account = $this->marketplaceAccount($otherTenant);

        $this->actingAs($this->userForTenant($tenant))
            ->from('/listings/create')
            ->post('/listings', [
                'product_id' => $product->id,
                'marketplace_account_id' => $account->id,
            ])
            ->assertRedirect('/listings/create')
            ->assertSessionHasErrors('marketplace_account_id');

        $this->assertSame(0, Listing::query()->count());
    }

    public function test_duplicate_listing_for_same_product_and_marketplace_account_is_blocked(): void
    {
        $tenant = $this->tenant('tenant-one');
        $product = $this->productWithVariants($tenant, 'Test Ürünü');
        $account = $this->marketplaceAccount($tenant);
        $this->listing($tenant, 'Test Ürünü', $product, $account);

        $this->actingAs($this->userForTenant($tenant))
            ->from('/listings/create')
            ->post('/listings', [
                'product_id' => $product->id,
                'marketplace_account_id' => $account->id,
            ])
            ->assertRedirect('/listings/create')
            ->assertSessionHasErrors('product_id');

        $this->assertSame(1, Listing::query()->count());
    }

    public function test_listing_creation_records_initial_status_history_and_safe_payload_preview(): void
    {
        Http::preventStrayRequests();

        $tenant = $this->tenant('tenant-one');
        $product = $this->productWithVariants($tenant, 'Payload Ürünü');
        $account = $this->marketplaceAccount($tenant, [
            'supplier_id' => '123456',
            'api_key' => 'secret-api-key',
            'api_secret' => 'secret-api-secret',
        ]);

        $this->actingAs($this->userForTenant($tenant))
            ->post('/listings', [
                'product_id' => $product->id,
                'marketplace_account_id' => $account->id,
            ])
            ->assertRedirect();

        $listing = Listing::query()->firstOrFail();
        $history = ListingStatusHistory::query()->firstOrFail();
        $payload = ListingPayload::query()->firstOrFail();

        $this->assertSame($tenant->id, $history->tenant_id);
        $this->assertNull($history->old_status);
        $this->assertSame(ListingStatus::Draft, $history->new_status);
        $this->assertSame($tenant->id, $payload->tenant_id);
        $this->assertSame($listing->id, $payload->listing_id);
        $this->assertSame(ListingPayloadType::Preview, $payload->payload_type);
        $this->assertSame('Payload Ürünü', $payload->payload['product']['name']);
        $this->assertCount(2, $payload->payload['variants']);

        $encodedPayload = json_encode($payload->payload, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('secret-api-key', $encodedPayload);
        $this->assertStringNotContainsString('secret-api-secret', $encodedPayload);
        $this->assertStringNotContainsString('supplier_id', $encodedPayload);
        $this->assertStringNotContainsString('api_secret', $encodedPayload);
    }

    /**
     * @return array<string, string>
     */
    private function inertiaHeaders(): array
    {
        config(['app.asset_url' => 'testing']);

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash('xxh128', 'testing'),
        ];
    }

    private function listing(
        Tenant $tenant,
        string $productName,
        ?Product $product = null,
        ?MarketplaceAccount $account = null,
    ): Listing {
        $product ??= $this->productWithVariants($tenant, $productName);
        $account ??= $this->marketplaceAccount($tenant);

        return Listing::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_account_id' => $account->id,
            'marketplace_id' => $account->marketplace_id,
            'product_id' => $product->id,
            'title' => $product->name,
            'status' => ListingStatus::Draft,
        ]);
    }

    private function productWithVariants(Tenant $tenant, string $name): Product
    {
        $product = Product::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => str($name)->slug()->append('-', str()->random(6))->toString(),
            'description' => 'Test açıklaması',
            'status' => ProductStatus::Active,
        ]);

        foreach (['SKU-1', 'SKU-2'] as $index => $sku) {
            $variant = ProductVariant::query()->create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'sku' => $sku.'-'.$tenant->id,
                'barcode' => '869000000000'.$index,
                'name' => 'Varyant '.($index + 1),
                'status' => ProductVariantStatus::Active,
            ]);

            ProductPrice::query()->create([
                'tenant_id' => $tenant->id,
                'product_variant_id' => $variant->id,
                'currency' => 'TRY',
                'sale_price' => '100.00',
            ]);

            ProductStock::query()->create([
                'tenant_id' => $tenant->id,
                'product_variant_id' => $variant->id,
                'quantity' => 10,
            ]);
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>|null  $credentials
     */
    private function marketplaceAccount(Tenant $tenant, ?array $credentials = null): MarketplaceAccount
    {
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        return MarketplaceAccount::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_id' => $marketplace->id,
            'name' => 'Trendyol Hesabı '.$tenant->id,
            'status' => MarketplaceAccountStatus::Active,
            'credentials' => $credentials ?? [
                'supplier_id' => '123456',
                'api_key' => 'api-key',
                'api_secret' => 'api-secret',
            ],
        ]);
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::query()->create([
            'name' => str($slug)->headline()->toString(),
            'slug' => $slug,
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);
    }

    private function userForTenant(Tenant $tenant): User
    {
        return User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
    }
}
