<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'product_variant_id', 'currency', 'sale_price', 'list_price'])]
class ProductPrice extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'list_price' => 'decimal:2',
        ];
    }
}
