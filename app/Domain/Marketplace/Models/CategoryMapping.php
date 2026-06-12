<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Category;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'category_id', 'marketplace_id', 'marketplace_category_id', 'status', 'notes'])]
class CategoryMapping extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceCategory(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class);
    }

    protected function casts(): array
    {
        return [
            'status' => MetadataMappingStatus::class,
        ];
    }
}
