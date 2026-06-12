<?php

namespace Tests\Feature;

use App\Domain\Catalog\Enums\AttributeType;
use App\Domain\Catalog\Enums\CatalogStatus;
use App\Domain\Catalog\Models\Attribute as CatalogAttribute;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Enums\MetadataEntityType;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use App\Domain\Marketplace\Models\BrandMapping;
use App\Domain\Marketplace\Models\CategoryMapping;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAttribute;
use App\Domain\Marketplace\Models\MarketplaceBrand;
use App\Domain\Marketplace\Models\MarketplaceCategory;
use App\Domain\Marketplace\Models\MetadataSyncRun;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketplaceMetadataMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_mapping_page(): void
    {
        $this->get('/marketplace-mappings/categories')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_own_tenant_mapping_list(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $marketplaceCategory = $this->marketplaceCategory('TY-100', 'Telefon');
        $visibleCategory = $this->category($tenant, 'Telefonlar');
        $hiddenCategory = $this->category($otherTenant, 'Gizli Kategori');

        CategoryMapping::query()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $visibleCategory->id,
            'marketplace_id' => $marketplaceCategory->marketplace_id,
            'marketplace_category_id' => $marketplaceCategory->id,
            'status' => MetadataMappingStatus::Mapped,
        ]);

        CategoryMapping::query()->create([
            'tenant_id' => $otherTenant->id,
            'category_id' => $hiddenCategory->id,
            'marketplace_id' => $marketplaceCategory->marketplace_id,
            'marketplace_category_id' => $marketplaceCategory->id,
            'status' => MetadataMappingStatus::Mapped,
        ]);

        $response = $this->actingAs($this->userForTenant($tenant))
            ->withHeaders($this->inertiaHeaders())
            ->get('/marketplace-mappings/categories')
            ->assertOk();

        $mappings = $response->json('props.mappings');

        $this->assertCount(1, $mappings);
        $this->assertSame('Telefonlar', $mappings[0]['category']['name']);
        $this->assertStringNotContainsString('Gizli Kategori', $response->getContent());
    }

    public function test_category_mapping_can_be_created_for_authenticated_users_tenant(): void
    {
        Http::preventStrayRequests();

        $tenant = $this->tenant('tenant-one');
        $category = $this->category($tenant, 'Ayakkabı');
        $marketplaceCategory = $this->marketplaceCategory('TY-200', 'Sneaker');

        $this->actingAs($this->userForTenant($tenant))
            ->post('/marketplace-mappings/categories', [
                'category_id' => $category->id,
                'marketplace_category_id' => $marketplaceCategory->id,
                'status' => MetadataMappingStatus::Mapped->value,
                'notes' => 'Ana kategori eşleşmesi',
            ])
            ->assertRedirect('/marketplace-mappings/categories');

        $mapping = CategoryMapping::query()->firstOrFail();

        $this->assertSame($tenant->id, $mapping->tenant_id);
        $this->assertSame($category->id, $mapping->category_id);
        $this->assertSame($marketplaceCategory->marketplace_id, $mapping->marketplace_id);
        $this->assertSame($marketplaceCategory->id, $mapping->marketplace_category_id);
        $this->assertSame(MetadataMappingStatus::Mapped, $mapping->status);
    }

    public function test_brand_mapping_can_be_created_for_authenticated_users_tenant(): void
    {
        Http::preventStrayRequests();

        $tenant = $this->tenant('tenant-one');
        $brand = $this->brand($tenant, 'Marka A');
        $marketplaceBrand = $this->marketplaceBrand('HB-100', 'Market Marka');

        $this->actingAs($this->userForTenant($tenant))
            ->post('/marketplace-mappings/brands', [
                'brand_id' => $brand->id,
                'marketplace_brand_id' => $marketplaceBrand->id,
                'status' => MetadataMappingStatus::Mapped->value,
            ])
            ->assertRedirect('/marketplace-mappings/brands');

        $mapping = BrandMapping::query()->firstOrFail();

        $this->assertSame($tenant->id, $mapping->tenant_id);
        $this->assertSame($brand->id, $mapping->brand_id);
        $this->assertSame($marketplaceBrand->marketplace_id, $mapping->marketplace_id);
        $this->assertSame($marketplaceBrand->id, $mapping->marketplace_brand_id);
    }

    public function test_other_tenant_catalog_entities_cannot_be_used_for_mappings(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $marketplaceCategory = $this->marketplaceCategory('TY-300', 'Çanta');
        $marketplaceBrand = $this->marketplaceBrand('TY-BRAND-300', 'Pazaryeri Marka');
        $marketplaceAttribute = $this->marketplaceAttribute('TY-ATTR-300', 'Renk');

        $this->actingAs($this->userForTenant($tenant))
            ->from('/marketplace-mappings/categories')
            ->post('/marketplace-mappings/categories', [
                'category_id' => $this->category($otherTenant, 'Başka Tenant Kategorisi')->id,
                'marketplace_category_id' => $marketplaceCategory->id,
                'status' => MetadataMappingStatus::Mapped->value,
            ])
            ->assertRedirect('/marketplace-mappings/categories')
            ->assertSessionHasErrors('category_id');

        $this->actingAs($this->userForTenant($tenant))
            ->from('/marketplace-mappings/brands')
            ->post('/marketplace-mappings/brands', [
                'brand_id' => $this->brand($otherTenant, 'Başka Tenant Markası')->id,
                'marketplace_brand_id' => $marketplaceBrand->id,
                'status' => MetadataMappingStatus::Mapped->value,
            ])
            ->assertRedirect('/marketplace-mappings/brands')
            ->assertSessionHasErrors('brand_id');

        $this->actingAs($this->userForTenant($tenant))
            ->from('/marketplace-mappings/attributes')
            ->post('/marketplace-mappings/attributes', [
                'attribute_id' => $this->attribute($otherTenant, 'Renk', 'color')->id,
                'marketplace_attribute_id' => $marketplaceAttribute->id,
                'status' => MetadataMappingStatus::Mapped->value,
            ])
            ->assertRedirect('/marketplace-mappings/attributes')
            ->assertSessionHasErrors('attribute_id');

        $this->assertSame(0, CategoryMapping::query()->count());
        $this->assertSame(0, BrandMapping::query()->count());
    }

    public function test_metadata_sync_run_and_items_can_be_created_without_http_calls(): void
    {
        Http::preventStrayRequests();

        $tenant = $this->tenant('tenant-one');
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        $run = MetadataSyncRun::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_id' => $marketplace->id,
            'entity_type' => MetadataEntityType::Category,
            'status' => MetadataSyncStatus::Pending,
            'summary' => [
                'created' => 0,
                'updated' => 0,
            ],
        ]);

        $item = $run->items()->create([
            'tenant_id' => $tenant->id,
            'entity_type' => MetadataEntityType::Category,
            'external_id' => 'TY-100',
            'status' => MetadataSyncStatus::Succeeded,
            'metadata' => [
                'name' => 'Telefon',
            ],
        ]);

        $this->assertSame($tenant->id, $run->tenant_id);
        $this->assertSame(MetadataEntityType::Category, $run->entity_type);
        $this->assertSame(MetadataSyncStatus::Pending, $run->status);
        $this->assertSame($run->id, $item->metadata_sync_run_id);
        $this->assertSame($tenant->id, $item->tenant_id);
        $this->assertSame(MetadataSyncStatus::Succeeded, $item->status);
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

    private function category(Tenant $tenant, string $name): Category
    {
        return Category::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'status' => CatalogStatus::Active,
        ]);
    }

    private function brand(Tenant $tenant, string $name): Brand
    {
        return Brand::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'status' => CatalogStatus::Active,
        ]);
    }

    private function attribute(Tenant $tenant, string $name, string $code): CatalogAttribute
    {
        return CatalogAttribute::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'code' => $code,
            'type' => AttributeType::Select,
            'status' => CatalogStatus::Active,
        ]);
    }

    private function marketplaceCategory(string $externalId, string $name): MarketplaceCategory
    {
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        return MarketplaceCategory::query()->create([
            'marketplace_id' => $marketplace->id,
            'external_id' => $externalId,
            'name' => $name,
            'path' => "Pazaryeri / {$name}",
        ]);
    }

    private function marketplaceBrand(string $externalId, string $name): MarketplaceBrand
    {
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Hepsiburada->value)->firstOrFail();

        return MarketplaceBrand::query()->create([
            'marketplace_id' => $marketplace->id,
            'external_id' => $externalId,
            'name' => $name,
        ]);
    }

    private function marketplaceAttribute(string $externalId, string $name): MarketplaceAttribute
    {
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        return MarketplaceAttribute::query()->create([
            'marketplace_id' => $marketplace->id,
            'external_id' => $externalId,
            'name' => $name,
            'code' => str($name)->slug('_')->toString(),
            'type' => 'select',
        ]);
    }
}
