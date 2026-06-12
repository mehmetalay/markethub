<?php

namespace Tests\Feature;

use App\Domain\Marketplace\Actions\RecordMarketplaceConnectionCheck;
use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Enums\MarketplaceConnectionStatus;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketplaceConnectionCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_connection_check_action_records_result_without_http_call(): void
    {
        Http::preventStrayRequests();

        $tenant = Tenant::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'status' => TenantStatus::Active,
            'timezone' => 'UTC',
        ]);

        $marketplace = Marketplace::query()->where('code', MarketplaceCode::Hepsiburada->value)->firstOrFail();

        $account = MarketplaceAccount::query()->create([
            'tenant_id' => $tenant->id,
            'marketplace_id' => $marketplace->id,
            'name' => 'Hepsiburada Ana Hesap',
            'status' => MarketplaceAccountStatus::Draft,
            'credentials' => [
                'api_key' => 'connection-key',
                'api_secret' => 'connection-secret',
            ],
        ]);

        $check = app(RecordMarketplaceConnectionCheck::class)->execute($account);

        $this->assertSame($tenant->id, $check->tenant_id);
        $this->assertSame($account->id, $check->marketplace_account_id);
        $this->assertSame(MarketplaceConnectionStatus::Pending, $check->status);
        $this->assertSame('hepsiburada', $check->metadata['provider']['code']);
        $this->assertSame($tenant->id, $check->metadata['context']['tenant_id']);
        $this->assertSame($account->id, $check->metadata['context']['marketplace_account_id']);

        $this->assertStringNotContainsString('connection-key', json_encode($check->metadata, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('connection-secret', json_encode($check->metadata, JSON_THROW_ON_ERROR));

        $rawMetadata = DB::table('marketplace_connection_checks')->value('metadata');

        $this->assertIsString($rawMetadata);
        $this->assertStringNotContainsString('connection-key', $rawMetadata);
        $this->assertStringNotContainsString('connection-secret', $rawMetadata);
    }
}
