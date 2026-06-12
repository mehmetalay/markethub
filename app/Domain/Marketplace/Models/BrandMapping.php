<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'brand_id', 'marketplace_id', 'marketplace_brand_id', 'status', 'notes'])]
class BrandMapping extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceBrand(): BelongsTo
    {
        return $this->belongsTo(MarketplaceBrand::class);
    }

    protected function casts(): array
    {
        return [
            'status' => MetadataMappingStatus::class,
        ];
    }
}
