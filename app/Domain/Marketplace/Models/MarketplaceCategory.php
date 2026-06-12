<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['marketplace_id', 'external_id', 'parent_external_id', 'name', 'path', 'is_active', 'raw_metadata'])]
class MarketplaceCategory extends Model
{
    use HasFactory;

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(MarketplaceAttribute::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(CategoryMapping::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'raw_metadata' => 'array',
        ];
    }
}
