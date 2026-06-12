<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Enums\CatalogStatus;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Enums\ProductVariantStatus;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductPrice;
use App\Domain\Catalog\Models\ProductStock;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('Products/Index', [
            'products' => Product::query()
                ->with(['brand', 'category', 'variants.prices', 'variants.stock'])
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (Product $product): array => $this->productListPayload($product)),
        ]);
    }

    public function create(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('Products/Create', $this->formOptions($tenantId));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $data = $this->validatedProductData($request, $tenantId);

        DB::transaction(function () use ($data, $tenantId): void {
            $product = Product::query()->create([
                'tenant_id' => $tenantId,
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueProductSlug($tenantId, $data['name']),
                'description' => $data['description'] ?? null,
                'status' => ProductStatus::from($data['status']),
            ]);

            $variant = ProductVariant::query()->create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'name' => $data['variant_name'] ?? null,
                'status' => ProductVariantStatus::Active,
            ]);

            ProductPrice::query()->create([
                'tenant_id' => $tenantId,
                'product_variant_id' => $variant->id,
                'currency' => $data['currency'],
                'sale_price' => $data['sale_price'],
                'list_price' => $data['list_price'] ?? null,
            ]);

            ProductStock::query()->create([
                'tenant_id' => $tenantId,
                'product_variant_id' => $variant->id,
                'quantity' => $data['quantity'],
                'reserved_quantity' => 0,
            ]);
        });

        return redirect()->route('products.index');
    }

    public function edit(Request $request, int $product): Response
    {
        $tenantId = $this->tenantId($request);
        $productModel = $this->tenantProduct($tenantId, $product);

        return Inertia::render('Products/Edit', [
            ...$this->formOptions($tenantId),
            'product' => $this->productFormPayload($productModel),
        ]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $productModel = $this->tenantProduct($tenantId, $product);
        $variant = $productModel->variants()->oldest()->first();

        $data = $this->validatedProductData($request, $tenantId, $variant?->id);

        DB::transaction(function () use ($data, $productModel, $tenantId, $variant): void {
            $productModel->update([
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueProductSlug($tenantId, $data['name'], $productModel->id),
                'description' => $data['description'] ?? null,
                'status' => ProductStatus::from($data['status']),
            ]);

            $variant ??= ProductVariant::query()->create([
                'tenant_id' => $tenantId,
                'product_id' => $productModel->id,
                'sku' => $data['sku'],
                'status' => ProductVariantStatus::Active,
            ]);

            $variant->update([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'name' => $data['variant_name'] ?? null,
                'status' => ProductVariantStatus::Active,
            ]);

            ProductPrice::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'product_variant_id' => $variant->id,
                    'currency' => $data['currency'],
                ],
                [
                    'sale_price' => $data['sale_price'],
                    'list_price' => $data['list_price'] ?? null,
                ],
            );

            ProductStock::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'quantity' => $data['quantity'],
                    'reserved_quantity' => 0,
                ],
            );
        });

        return redirect()->route('products.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProductData(Request $request, int $tenantId, ?int $ignoreVariantId = null): array
    {
        return $request->validate([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId),
            ],
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')->where('tenant_id', $tenantId),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'sku' => [
                'required',
                'string',
                'max:120',
                Rule::unique('product_variants', 'sku')
                    ->where('tenant_id', $tenantId)
                    ->ignore($ignoreVariantId),
            ],
            'barcode' => ['nullable', 'string', 'max:120'],
            'variant_name' => ['nullable', 'string', 'max:200'],
            'currency' => ['required', 'string', 'size:3'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            'category_id.exists' => 'Seçilen kategori geçerli değil.',
            'brand_id.exists' => 'Seçilen marka geçerli değil.',
            'name.required' => 'Ürün adı zorunludur.',
            'name.max' => 'Ürün adı en fazla 200 karakter olabilir.',
            'description.max' => 'Açıklama en fazla 5000 karakter olabilir.',
            'status.required' => 'Ürün durumu zorunludur.',
            'sku.required' => 'SKU zorunludur.',
            'sku.max' => 'SKU en fazla 120 karakter olabilir.',
            'sku.unique' => 'Bu SKU aynı çalışma alanında zaten kullanılıyor.',
            'barcode.max' => 'Barkod en fazla 120 karakter olabilir.',
            'variant_name.max' => 'Varyant adı en fazla 200 karakter olabilir.',
            'currency.required' => 'Para birimi zorunludur.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır.',
            'sale_price.required' => 'Satış fiyatı zorunludur.',
            'sale_price.numeric' => 'Satış fiyatı sayısal olmalıdır.',
            'sale_price.min' => 'Satış fiyatı negatif olamaz.',
            'list_price.numeric' => 'Liste fiyatı sayısal olmalıdır.',
            'list_price.min' => 'Liste fiyatı negatif olamaz.',
            'quantity.required' => 'Stok miktarı zorunludur.',
            'quantity.integer' => 'Stok miktarı tam sayı olmalıdır.',
            'quantity.min' => 'Stok miktarı negatif olamaz.',
        ]);
    }

    /**
     * @return array{categories: list<array{id: int, name: string}>, brands: list<array{id: int, name: string}>, statuses: list<array{value: string, label: string}>}
     */
    private function formOptions(int $tenantId): array
    {
        return [
            'categories' => Category::query()
                ->where('tenant_id', $tenantId)
                ->where('status', CatalogStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->all(),
            'brands' => Brand::query()
                ->where('tenant_id', $tenantId)
                ->where('status', CatalogStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ])
                ->all(),
            'statuses' => [
                ['value' => ProductStatus::Draft->value, 'label' => 'Taslak'],
                ['value' => ProductStatus::Active->value, 'label' => 'Aktif'],
                ['value' => ProductStatus::Inactive->value, 'label' => 'Pasif'],
                ['value' => ProductStatus::Archived->value, 'label' => 'Arşiv'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productListPayload(Product $product): array
    {
        $variant = $product->variants->first();
        $price = $variant?->prices->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'status' => $product->status->value,
            'sku' => $variant?->sku,
            'sale_price' => $price?->sale_price,
            'currency' => $price?->currency,
            'quantity' => $variant?->stock?->quantity,
            'created_at' => $product->created_at?->toISOString(),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productFormPayload(Product $product): array
    {
        $product->loadMissing(['variants.prices', 'variants.stock']);
        $variant = $product->variants->first();
        $price = $variant?->prices->first();

        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status->value,
            'sku' => $variant?->sku,
            'barcode' => $variant?->barcode,
            'variant_name' => $variant?->name,
            'currency' => $price?->currency ?? 'TRY',
            'sale_price' => $price?->sale_price,
            'list_price' => $price?->list_price,
            'quantity' => $variant?->stock?->quantity ?? 0,
        ];
    }

    private function uniqueProductSlug(int $tenantId, string $name, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 2;

        while (Product::query()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function tenantProduct(int $tenantId, int $productId): Product
    {
        return Product::query()
            ->with(['variants.prices', 'variants.stock'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($productId);
    }

    private function tenantId(Request $request): int
    {
        $tenantId = $request->user()?->tenant_id;

        abort_unless($tenantId, 403);

        return $tenantId;
    }
}
