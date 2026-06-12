<?php

namespace App\Domain\Listing\Models;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Listing\Enums\ListingVariantStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'listing_id', 'product_variant_id', 'sku', 'external_id', 'status'])]
class ListingVariant extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ListingError::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ListingVariantStatus::class,
        ];
    }
}
