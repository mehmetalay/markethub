<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['marketplace_id', 'marketplace_attribute_id', 'external_id', 'value', 'code', 'sort_order', 'raw_metadata'])]
class MarketplaceAttributeValue extends Model
{
    use HasFactory;

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceAttribute(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAttribute::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(AttributeValueMapping::class);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'raw_metadata' => 'array',
        ];
    }
}
