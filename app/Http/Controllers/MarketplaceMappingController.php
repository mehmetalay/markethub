<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Attribute as CatalogAttribute;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Marketplace\Models\AttributeMapping;
use App\Domain\Marketplace\Models\BrandMapping;
use App\Domain\Marketplace\Models\CategoryMapping;
use App\Domain\Marketplace\Models\MarketplaceAttribute;
use App\Domain\Marketplace\Models\MarketplaceBrand;
use App\Domain\Marketplace\Models\MarketplaceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceMappingController extends Controller
{
    public function categories(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('MarketplaceMappings/Categories', [
            'mappings' => CategoryMapping::query()
                ->with(['category', 'marketplaceCategory.marketplace'])
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (CategoryMapping $mapping): array => $this->categoryMappingPayload($mapping)),
            'categories' => $this->categoryOptions($tenantId),
            'marketplaceCategories' => $this->marketplaceCategoryOptions(),
            'statuses' => $this->mappingStatuses(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $data = $this->validatedCategoryMapping($request, $tenantId);
        $marketplaceCategory = MarketplaceCategory::query()->findOrFail($data['marketplace_category_id']);

        CategoryMapping::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'marketplace_category_id' => $marketplaceCategory->id,
            ],
            [
                'category_id' => $data['category_id'],
                'marketplace_id' => $marketplaceCategory->marketplace_id,
                'status' => MetadataMappingStatus::from($data['status']),
                'notes' => $data['notes'] ?? null,
            ],
        );

        return redirect()->route('marketplace-mappings.categories');
    }

    public function updateCategory(Request $request, int $mapping): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $categoryMapping = $this->tenantCategoryMapping($tenantId, $mapping);
        $data = $this->validatedCategoryMapping($request, $tenantId);
        $marketplaceCategory = MarketplaceCategory::query()->findOrFail($data['marketplace_category_id']);

        $categoryMapping->update([
            'category_id' => $data['category_id'],
            'marketplace_id' => $marketplaceCategory->marketplace_id,
            'marketplace_category_id' => $marketplaceCategory->id,
            'status' => MetadataMappingStatus::from($data['status']),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('marketplace-mappings.categories');
    }

    public function brands(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('MarketplaceMappings/Brands', [
            'mappings' => BrandMapping::query()
                ->with(['brand', 'marketplaceBrand.marketplace'])
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (BrandMapping $mapping): array => $this->brandMappingPayload($mapping)),
            'brands' => $this->brandOptions($tenantId),
            'marketplaceBrands' => $this->marketplaceBrandOptions(),
            'statuses' => $this->mappingStatuses(),
        ]);
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $data = $this->validatedBrandMapping($request, $tenantId);
        $marketplaceBrand = MarketplaceBrand::query()->findOrFail($data['marketplace_brand_id']);

        BrandMapping::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'marketplace_brand_id' => $marketplaceBrand->id,
            ],
            [
                'brand_id' => $data['brand_id'],
                'marketplace_id' => $marketplaceBrand->marketplace_id,
                'status' => MetadataMappingStatus::from($data['status']),
                'notes' => $data['notes'] ?? null,
            ],
        );

        return redirect()->route('marketplace-mappings.brands');
    }

    public function updateBrand(Request $request, int $mapping): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $brandMapping = $this->tenantBrandMapping($tenantId, $mapping);
        $data = $this->validatedBrandMapping($request, $tenantId);
        $marketplaceBrand = MarketplaceBrand::query()->findOrFail($data['marketplace_brand_id']);

        $brandMapping->update([
            'brand_id' => $data['brand_id'],
            'marketplace_id' => $marketplaceBrand->marketplace_id,
            'marketplace_brand_id' => $marketplaceBrand->id,
            'status' => MetadataMappingStatus::from($data['status']),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('marketplace-mappings.brands');
    }

    public function attributes(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('MarketplaceMappings/Attributes', [
            'mappings' => AttributeMapping::query()
                ->with(['attribute', 'marketplaceAttribute.marketplace'])
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (AttributeMapping $mapping): array => $this->attributeMappingPayload($mapping)),
            'attributes' => $this->attributeOptions($tenantId),
            'marketplaceAttributes' => $this->marketplaceAttributeOptions(),
            'statuses' => $this->mappingStatuses(),
        ]);
    }

    public function storeAttribute(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $data = $this->validatedAttributeMapping($request, $tenantId);
        $marketplaceAttribute = MarketplaceAttribute::query()->findOrFail($data['marketplace_attribute_id']);

        AttributeMapping::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'marketplace_attribute_id' => $marketplaceAttribute->id,
            ],
            [
                'attribute_id' => $data['attribute_id'],
                'marketplace_id' => $marketplaceAttribute->marketplace_id,
                'status' => MetadataMappingStatus::from($data['status']),
                'notes' => $data['notes'] ?? null,
            ],
        );

        return redirect()->route('marketplace-mappings.attributes');
    }

    public function updateAttribute(Request $request, int $mapping): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $attributeMapping = $this->tenantAttributeMapping($tenantId, $mapping);
        $data = $this->validatedAttributeMapping($request, $tenantId);
        $marketplaceAttribute = MarketplaceAttribute::query()->findOrFail($data['marketplace_attribute_id']);

        $attributeMapping->update([
            'attribute_id' => $data['attribute_id'],
            'marketplace_id' => $marketplaceAttribute->marketplace_id,
            'marketplace_attribute_id' => $marketplaceAttribute->id,
            'status' => MetadataMappingStatus::from($data['status']),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('marketplace-mappings.attributes');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCategoryMapping(Request $request, int $tenantId): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('tenant_id', $tenantId)],
            'marketplace_category_id' => ['required', 'integer', Rule::exists('marketplace_categories', 'id')],
            'status' => ['required', Rule::enum(MetadataMappingStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], $this->validationMessages('category'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedBrandMapping(Request $request, int $tenantId): array
    {
        return $request->validate([
            'brand_id' => ['required', 'integer', Rule::exists('brands', 'id')->where('tenant_id', $tenantId)],
            'marketplace_brand_id' => ['required', 'integer', Rule::exists('marketplace_brands', 'id')],
            'status' => ['required', Rule::enum(MetadataMappingStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], $this->validationMessages('brand'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAttributeMapping(Request $request, int $tenantId): array
    {
        return $request->validate([
            'attribute_id' => ['required', 'integer', Rule::exists('attributes', 'id')->where('tenant_id', $tenantId)],
            'marketplace_attribute_id' => ['required', 'integer', Rule::exists('marketplace_attributes', 'id')],
            'status' => ['required', Rule::enum(MetadataMappingStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], $this->validationMessages('attribute'));
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(string $type): array
    {
        $localName = match ($type) {
            'brand' => 'Marka',
            'attribute' => 'Attribute',
            default => 'Kategori',
        };

        $marketplaceName = match ($type) {
            'brand' => 'pazaryeri markası',
            'attribute' => 'pazaryeri attribute kaydı',
            default => 'pazaryeri kategorisi',
        };

        return [
            "{$type}_id.required" => "{$localName} seçimi zorunludur.",
            "{$type}_id.exists" => "Seçilen {$localName} bu çalışma alanına ait değil.",
            "marketplace_{$type}_id.required" => ucfirst($marketplaceName).' seçimi zorunludur.',
            "marketplace_{$type}_id.exists" => 'Seçilen '.$marketplaceName.' geçerli değil.',
            'status.required' => 'Eşleştirme durumu zorunludur.',
            'notes.max' => 'Not en fazla 1000 karakter olabilir.',
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function mappingStatuses(): array
    {
        return [
            ['value' => MetadataMappingStatus::Mapped->value, 'label' => 'Eşleşti'],
            ['value' => MetadataMappingStatus::Pending->value, 'label' => 'Beklemede'],
            ['value' => MetadataMappingStatus::Ignored->value, 'label' => 'Yok sayıldı'],
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function categoryOptions(int $tenantId): array
    {
        return Category::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function brandOptions(int $tenantId): array
    {
        return Brand::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Brand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, code: string}>
     */
    private function attributeOptions(int $tenantId): array
    {
        return CatalogAttribute::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (CatalogAttribute $attribute): array => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, marketplace: string}>
     */
    private function marketplaceCategoryOptions(): array
    {
        return MarketplaceCategory::query()
            ->with('marketplace')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (MarketplaceCategory $category): array => [
                'id' => $category->id,
                'name' => $category->path ?: $category->name,
                'marketplace' => $category->marketplace->name,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, marketplace: string}>
     */
    private function marketplaceBrandOptions(): array
    {
        return MarketplaceBrand::query()
            ->with('marketplace')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (MarketplaceBrand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'marketplace' => $brand->marketplace->name,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, marketplace: string}>
     */
    private function marketplaceAttributeOptions(): array
    {
        return MarketplaceAttribute::query()
            ->with('marketplace')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (MarketplaceAttribute $attribute): array => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'marketplace' => $attribute->marketplace->name,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryMappingPayload(CategoryMapping $mapping): array
    {
        return [
            'id' => $mapping->id,
            'status' => $mapping->status->value,
            'notes' => $mapping->notes,
            'category' => [
                'id' => $mapping->category->id,
                'name' => $mapping->category->name,
            ],
            'marketplaceCategory' => [
                'id' => $mapping->marketplaceCategory->id,
                'name' => $mapping->marketplaceCategory->name,
                'path' => $mapping->marketplaceCategory->path,
                'marketplace' => $mapping->marketplaceCategory->marketplace->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function brandMappingPayload(BrandMapping $mapping): array
    {
        return [
            'id' => $mapping->id,
            'status' => $mapping->status->value,
            'notes' => $mapping->notes,
            'brand' => [
                'id' => $mapping->brand->id,
                'name' => $mapping->brand->name,
            ],
            'marketplaceBrand' => [
                'id' => $mapping->marketplaceBrand->id,
                'name' => $mapping->marketplaceBrand->name,
                'marketplace' => $mapping->marketplaceBrand->marketplace->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributeMappingPayload(AttributeMapping $mapping): array
    {
        return [
            'id' => $mapping->id,
            'status' => $mapping->status->value,
            'notes' => $mapping->notes,
            'attribute' => [
                'id' => $mapping->attribute->id,
                'name' => $mapping->attribute->name,
                'code' => $mapping->attribute->code,
            ],
            'marketplaceAttribute' => [
                'id' => $mapping->marketplaceAttribute->id,
                'name' => $mapping->marketplaceAttribute->name,
                'marketplace' => $mapping->marketplaceAttribute->marketplace->name,
            ],
        ];
    }

    private function tenantCategoryMapping(int $tenantId, int $mappingId): CategoryMapping
    {
        return CategoryMapping::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($mappingId);
    }

    private function tenantBrandMapping(int $tenantId, int $mappingId): BrandMapping
    {
        return BrandMapping::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($mappingId);
    }

    private function tenantAttributeMapping(int $tenantId, int $mappingId): AttributeMapping
    {
        return AttributeMapping::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($mappingId);
    }

    private function tenantId(Request $request): int
    {
        $tenantId = $request->user()?->tenant_id;

        abort_unless($tenantId, 403);

        return $tenantId;
    }
}
