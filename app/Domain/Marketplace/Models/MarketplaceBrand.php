<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['marketplace_id', 'external_id', 'name', 'is_active', 'raw_metadata'])]
class MarketplaceBrand extends Model
{
    use HasFactory;

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(BrandMapping::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'raw_metadata' => 'array',
        ];
    }
}
