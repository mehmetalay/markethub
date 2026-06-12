<?php

namespace App\Domain\Listing\Models;

use App\Domain\Listing\Enums\ListingPayloadType;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'listing_id', 'payload_type', 'payload', 'generated_at'])]
class ListingPayload extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    protected function casts(): array
    {
        return [
            'payload_type' => ListingPayloadType::class,
            'payload' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
