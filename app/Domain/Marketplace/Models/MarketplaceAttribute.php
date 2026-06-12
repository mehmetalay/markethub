<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['marketplace_id', 'marketplace_category_id', 'external_id', 'name', 'code', 'type', 'is_required', 'is_active', 'raw_metadata'])]
class MarketplaceAttribute extends Model
{
    use HasFactory;

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceCategory(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(MarketplaceAttributeValue::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(AttributeMapping::class);
    }

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'raw_metadata' => 'array',
        ];
    }
}
