<?php

namespace App\Domain\Marketplace\Models;

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

    protected function casts(): array
    {
        return [
            'code' => MarketplaceCode::class,
            'is_active' => 'boolean',
        ];
    }
}
