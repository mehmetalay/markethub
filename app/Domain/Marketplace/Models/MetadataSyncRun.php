<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Marketplace\Enums\MetadataEntityType;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'marketplace_id', 'marketplace_account_id', 'entity_type', 'status', 'started_at', 'finished_at', 'message', 'summary'])]
class MetadataSyncRun extends Model
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

    public function marketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MetadataSyncRunItem::class);
    }

    protected function casts(): array
    {
        return [
            'entity_type' => MetadataEntityType::class,
            'status' => MetadataSyncStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'summary' => 'array',
        ];
    }
}
