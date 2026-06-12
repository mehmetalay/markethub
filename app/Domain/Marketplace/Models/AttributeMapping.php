<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Attribute as CatalogAttribute;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'attribute_id', 'marketplace_id', 'marketplace_attribute_id', 'status', 'notes'])]
class AttributeMapping extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CatalogAttribute::class, 'attribute_id');
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceAttribute(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAttribute::class);
    }

    public function valueMappings(): HasMany
    {
        return $this->hasMany(AttributeValueMapping::class);
    }

    protected function casts(): array
    {
        return [
            'status' => MetadataMappingStatus::class,
        ];
    }
}
