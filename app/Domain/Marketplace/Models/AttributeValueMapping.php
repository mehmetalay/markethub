<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'attribute_mapping_id', 'attribute_value_id', 'marketplace_id', 'marketplace_attribute_value_id', 'status', 'notes'])]
class AttributeValueMapping extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function attributeMapping(): BelongsTo
    {
        return $this->belongsTo(AttributeMapping::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function marketplaceAttributeValue(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAttributeValue::class);
    }

    protected function casts(): array
    {
        return [
            'status' => MetadataMappingStatus::class,
        ];
    }
}
