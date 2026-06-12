<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Catalog\Models\Attribute as CatalogAttribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductPrice;
use App\Domain\Catalog\Models\ProductStock;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Marketplace\Models\AttributeMapping;
use App\Domain\Marketplace\Models\AttributeValueMapping;
use App\Domain\Marketplace\Models\BrandMapping;
use App\Domain\Marketplace\Models\CategoryMapping;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceConnectionCheck;
use App\Domain\Marketplace\Models\MetadataSyncRun;
use App\Domain\Marketplace\Models\MetadataSyncRunItem;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'status', 'billing_email', 'timezone'])]
class Tenant extends Model
{
    use HasFactory;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function marketplaceAccounts(): HasMany
    {
        return $this->hasMany(MarketplaceAccount::class);
    }

    public function marketplaceConnectionChecks(): HasMany
    {
        return $this->hasMany(MarketplaceConnectionCheck::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function catalogAttributes(): HasMany
    {
        return $this->hasMany(CatalogAttribute::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function categoryMappings(): HasMany
    {
        return $this->hasMany(CategoryMapping::class);
    }

    public function brandMappings(): HasMany
    {
        return $this->hasMany(BrandMapping::class);
    }

    public function attributeMappings(): HasMany
    {
        return $this->hasMany(AttributeMapping::class);
    }

    public function attributeValueMappings(): HasMany
    {
        return $this->hasMany(AttributeValueMapping::class);
    }

    public function metadataSyncRuns(): HasMany
    {
        return $this->hasMany(MetadataSyncRun::class);
    }

    public function metadataSyncRunItems(): HasMany
    {
        return $this->hasMany(MetadataSyncRunItem::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
        ];
    }
}
