<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Marketplace\Enums\MetadataEntityType;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'metadata_sync_run_id', 'entity_type', 'external_id', 'status', 'message', 'metadata'])]
class MetadataSyncRunItem extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(MetadataSyncRun::class, 'metadata_sync_run_id');
    }

    protected function casts(): array
    {
        return [
            'entity_type' => MetadataEntityType::class,
            'status' => MetadataSyncStatus::class,
            'metadata' => 'array',
        ];
    }
}
