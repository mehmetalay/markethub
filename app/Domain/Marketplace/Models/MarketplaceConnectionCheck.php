<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Marketplace\Enums\MarketplaceConnectionStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'marketplace_account_id', 'status', 'checked_at', 'message', 'metadata'])]
class MarketplaceConnectionCheck extends Model
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

    protected function casts(): array
    {
        return [
            'status' => MarketplaceConnectionStatus::class,
            'checked_at' => 'datetime',
            'metadata' => 'encrypted:array',
        ];
    }
}
