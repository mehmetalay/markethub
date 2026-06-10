<?php

namespace Database\Seeders;

use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'markethub-internal'],
            [
                'name' => 'MarketHub Internal',
                'status' => TenantStatus::Active,
                'billing_email' => 'admin@markethub.local',
                'timezone' => 'Europe/Istanbul',
            ],
        );

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        $permissions = collect([
            'dashboard.view',
            'tenants.manage',
            'users.manage',
            'roles.manage',
            'marketplaces.view',
        ])->map(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $ownerRole = Role::firstOrCreate([
            'tenant_id' => $tenant->id,
            'name' => 'owner',
            'guard_name' => 'web',
        ]);

        $ownerRole->syncPermissions($permissions);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'MarketHub Admin',
            'email' => 'admin@markethub.local',
        ]);

        $user->assignRole($ownerRole);
    }
}
