<?php

namespace App\Domain\Listing\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Listing\Enums\ListingStatus;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'marketplace_account_id', 'marketplace_id', 'product_id', 'title', 'external_id', 'status', 'last_synced_at'])]
class Listing extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function marketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ListingVariant::class);
    }

    public function payloads(): HasMany
    {
        return $this->hasMany(ListingPayload::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ListingStatusHistory::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ListingError::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ListingStatus::class,
            'last_synced_at' => 'datetime',
        ];
    }
}
