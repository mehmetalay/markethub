<?php

namespace Tests\Feature;

use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketplaceAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_marketplace_accounts_page(): void
    {
        $this->get('/marketplace-accounts')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_marketplace_accounts_page(): void
    {
        $user = $this->userForTenant($this->tenant('tenant-one'));

        $this->actingAs($user)
            ->withHeaders($this->inertiaHeaders())
            ->get('/marketplace-accounts')
            ->assertOk();
    }

    public function test_marketplace_account_can_be_created_for_authenticated_users_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $user = $this->userForTenant($tenant);
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Trendyol->value)->firstOrFail();

        $this->actingAs($user)
            ->post('/marketplace-accounts', [
                'marketplace_id' => $marketplace->id,
                'name' => 'Trendyol Ana Hesap',
                'seller_id' => 'seller-123',
                'api_key' => 'public-api-key',
                'api_secret' => 'super-secret-value',
            ])
            ->assertRedirect('/marketplace-accounts');

        $account = MarketplaceAccount::query()->firstOrFail();

        $this->assertSame($tenant->id, $account->tenant_id);
        $this->assertSame($marketplace->id, $account->marketplace_id);
        $this->assertSame(MarketplaceAccountStatus::Draft, $account->status);
        $this->assertSame('super-secret-value', $account->credentials['api_secret']);

        $rawCredentials = DB::table('marketplace_accounts')->value('credentials');

        $this->assertIsString($rawCredentials);
        $this->assertStringNotContainsString('super-secret-value', $rawCredentials);
        $this->assertStringNotContainsString('public-api-key', $rawCredentials);
    }

    public function test_marketplace_accounts_are_limited_to_authenticated_users_tenant(): void
    {
        $tenant = $this->tenant('tenant-one');
        $otherTenant = $this->tenant('tenant-two');
        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Hepsiburada->value)->firstOrFail();

        MarketplaceAccount::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_id' => $marketplace->id,
            'name' => 'Görünen Hesap',
            'status' => MarketplaceAccountStatus::Draft,
            'credentials' => [
                'api_key' => 'visible-key',
                'api_secret' => 'visible-secret',
            ],
        ]);

        MarketplaceAccount::query()->create([
            'tenant_id' => $otherTenant->id,
            'marketplace_id' => $marketplace->id,
            'name' => 'Başka Tenant Hesabı',
            'status' => MarketplaceAccountStatus::Draft,
            'credentials' => [
                'api_key' => 'other-key',
                'api_secret' => 'other-secret',
            ],
        ]);

        $response = $this->actingAs($this->userForTenant($tenant))
            ->withHeaders($this->inertiaHeaders())
            ->get('/marketplace-accounts')
            ->assertOk();

        $accounts = $response->json('props.accounts');

        $this->assertCount(1, $accounts);
        $this->assertSame('Görünen Hesap', $accounts[0]['name']);
        $this->assertArrayNotHasKey('credentials', $accounts[0]);
        $this->assertStringNotContainsString('visible-key', $response->getContent());
        $this->assertStringNotContainsString('visible-secret', $response->getContent());
        $this->assertStringNotContainsString('other-secret', $response->getContent());
        $this->assertStringNotContainsString('other-key', $response->getContent());
    }

    /**
     * @return array<string, string>
     */
    private function inertiaHeaders(): array
    {
        config(['app.asset_url' => 'testing']);

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash('xxh128', 'testing'),
        ];
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::query()->create([
            'name' => str($slug)->headline()->toString(),
            'slug' => $slug,
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);
    }

    private function userForTenant(Tenant $tenant): User
    {
        return User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
    }
}
