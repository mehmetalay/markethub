<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Listing\Models\Listing;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'is_active'])]
class Marketplace extends Model
{
    use HasFactory;

    public function accounts(): HasMany
    {
        return $this->hasMany(MarketplaceAccount::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(MarketplaceCategory::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(MarketplaceBrand::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(MarketplaceAttribute::class);
    }

    public function metadataSyncRuns(): HasMany
    {
        return $this->hasMany(MetadataSyncRun::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    protected function casts(): array
    {
        return [
            'code' => MarketplaceCode::class,
            'is_active' => 'boolean',
        ];
    }
}
