<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'marketplace_id', 'name', 'status', 'credentials', 'settings'])]
#[Hidden(['credentials'])]
class MarketplaceAccount extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function connectionChecks(): HasMany
    {
        return $this->hasMany(MarketplaceConnectionCheck::class);
    }

    protected function casts(): array
    {
        return [
            'status' => MarketplaceAccountStatus::class,
            'credentials' => 'encrypted:array',
            'settings' => 'array',
        ];
    }
}
