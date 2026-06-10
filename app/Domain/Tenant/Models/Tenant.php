<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceConnectionCheck;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'status', 'billing_email', 'timezone'])]
class Tenant extends Model
{
    use HasFactory;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function marketplaceAccounts(): HasMany
    {
        return $this->hasMany(MarketplaceAccount::class);
    }

    public function marketplaceConnectionChecks(): HasMany
    {
        return $this->hasMany(MarketplaceConnectionCheck::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
        ];
    }
}
