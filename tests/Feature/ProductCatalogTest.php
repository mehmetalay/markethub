<?php

namespace Tests\Feature;

use App\Domain\Catalog\Enums\CatalogStatus;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Enums\ProductVariantStatus;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductPrice;
use App\Domain\Catalog\Models\ProductStock;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_products_page(): void
    {
        $this->get('/products')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_products_page(): void
    {
        $user = $this->userForTenant($this->tenant('tenant-one'));

        $this->actingAs($user)
            ->withHeaders($this->inertiaHeaders())
            ->get('/products')
            ->assertOk();
    }

    public function test_product_can_be_created_for_authenticated_users_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $user = $this->userForTenant($tenant);
        $category = $this->category($tenant, 'Telefonlar');
        $brand = $this->brand($tenant, 'Marka A');

        $this->actingAs($user)
            ->post('/products', $this->productPayload([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
            ]))
            ->assertRedirect('/products');

        $product = Product::query()->firstOrFail();
        $variant = ProductVariant::query()->firstOrFail();

        $this->assertSame($tenant->id, $product->tenant_id);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame($brand->id, $product->brand_id);
        $this->assertSame(ProductStatus::Active, $product->status);
        $this->assertSame($tenant->id, $variant->tenant_id);
        $this->assertSame($product->id, $variant->product_id);
        $this->assertSame('SKU-100', $variant->sku);
    }

    public function test_products_are_limited_to_authenticated_users_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');

        $visibleProduct = $this->productWithVariant($tenant, 'Görünen Ürün', 'SKU-VISIBLE');
        $this->productWithVariant($otherTenant, 'Başka Tenant Ürünü', 'SKU-HIDDEN');

        $response = $this->actingAs($this->userForTenant($tenant))
            ->withHeaders($this->inertiaHeaders())
            ->get('/products')
            ->assertOk();

        $products = $response->json('props.products');

        $this->assertCount(1, $products);
        $this->assertSame($visibleProduct->id, $products[0]['id']);
        $this->assertSame('Görünen Ürün', $products[0]['name']);
        $this->assertStringNotContainsString('Başka Tenant Ürünü', $response->getContent());
    }

    public function test_sku_must_be_unique_inside_same_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $this->productWithVariant($tenant, 'Mevcut Ürün', 'SKU-100');

        $this->actingAs($this->userForTenant($tenant))
            ->from('/products/create')
            ->post('/products', $this->productPayload())
            ->assertRedirect('/products/create')
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, ProductVariant::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_same_sku_can_be_used_by_different_tenants(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $this->productWithVariant($tenant, 'Tenant One Ürün', 'SKU-100');

        $this->actingAs($this->userForTenant($otherTenant))
            ->post('/products', $this->productPayload())
            ->assertRedirect('/products');

        $this->assertSame(2, ProductVariant::query()->where('sku', 'SKU-100')->count());
    }

    public function test_price_and_stock_are_created_for_variant(): void
    {
        $tenant = $this->tenant('tenant-one');

        $this->actingAs($this->userForTenant($tenant))
            ->post('/products', $this->productPayload([
                'sale_price' => '199.90',
                'list_price' => '249.90',
                'quantity' => 12,
            ]))
            ->assertRedirect('/products');

        $variant = ProductVariant::query()->firstOrFail();
        $price = ProductPrice::query()->firstOrFail();
        $stock = ProductStock::query()->firstOrFail();

        $this->assertSame($variant->id, $price->product_variant_id);
        $this->assertSame($tenant->id, $price->tenant_id);
        $this->assertSame('TRY', $price->currency);
        $this->assertSame('199.90', $price->sale_price);
        $this->assertSame('249.90', $price->list_price);
        $this->assertSame($variant->id, $stock->product_variant_id);
        $this->assertSame($tenant->id, $stock->tenant_id);
        $this->assertSame(12, $stock->quantity);
        $this->assertSame(0, $stock->reserved_quantity);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function productPayload(array $overrides = []): array
    {
        return [
            ...[
                'category_id' => null,
                'brand_id' => null,
                'name' => 'Test Ürünü',
                'description' => 'Katalog test ürünü',
                'status' => ProductStatus::Active->value,
                'sku' => 'SKU-100',
                'barcode' => '8690000000001',
                'variant_name' => 'Standart',
                'currency' => 'TRY',
                'sale_price' => '129.90',
                'list_price' => null,
                'quantity' => 5,
            ],
            ...$overrides,
        ];
    }

    private function productWithVariant(Tenant $tenant, string $name, string $sku): Product
    {
        $product = Product::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'status' => ProductStatus::Active,
        ]);

        $variant = ProductVariant::query()->create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'sku' => $sku,
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

        return $product;
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
}
