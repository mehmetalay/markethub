<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\AttributeType;
use App\Domain\Catalog\Enums\CatalogStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'name', 'code', 'type', 'status'])]
class Attribute extends Model
{
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'status' => CatalogStatus::class,
        ];
    }
}
