<?php

namespace Tests\Feature;

use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_registration_creates_tenant_owner_user_and_session(): void
    {
        $response = $this->post('/register', [
            'tenant_name' => 'Northwind Commerce',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $tenant = Tenant::query()->where('slug', 'northwind-commerce')->firstOrFail();
        $user = User::query()->where('email', 'ada@example.test')->firstOrFail();

        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('roles', [
            'tenant_id' => $tenant->id,
            'name' => 'owner',
            'guard_name' => 'web',
        ]);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        config(['app.asset_url' => 'testing']);

        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash('xxh128', 'testing'),
        ])
            ->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_sanctum_api_returns_current_user(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant Api',
            'slug' => 'tenant-api',
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.tenant_id', $tenant->id);
    }
}
