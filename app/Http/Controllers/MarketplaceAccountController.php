<?php

namespace App\Http\Controllers;

use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use App\Domain\Marketplace\Models\Marketplace;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('MarketplaceAccounts/Index', [
            'accounts' => MarketplaceAccount::query()
                ->with('marketplace')
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (MarketplaceAccount $account): array => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'status' => $account->status->value,
                    'created_at' => $account->created_at?->toISOString(),
                    'marketplace' => [
                        'id' => $account->marketplace->id,
                        'code' => $account->marketplace->code->value,
                        'name' => $account->marketplace->name,
                    ],
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MarketplaceAccounts/Create', [
            'marketplaces' => $this->activeMarketplaces(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);

        $data = $request->validate([
            'marketplace_id' => [
                'required',
                'integer',
                Rule::exists('marketplaces', 'id')->where('is_active', true),
            ],
            'name' => [
                'required',
                'string',
                'max:160',
                Rule::unique('marketplace_accounts', 'name')
                    ->where('tenant_id', $tenantId)
                    ->where('marketplace_id', $request->integer('marketplace_id')),
            ],
            'seller_id' => ['nullable', 'string', 'max:160'],
            'api_key' => ['required', 'string', 'max:500'],
            'api_secret' => ['required', 'string', 'max:500'],
        ], [
            'marketplace_id.required' => 'Pazaryeri seçimi zorunludur.',
            'marketplace_id.exists' => 'Seçilen pazaryeri geçerli değil.',
            'name.required' => 'Hesap adı zorunludur.',
            'name.max' => 'Hesap adı en fazla 160 karakter olabilir.',
            'name.unique' => 'Bu pazaryeri için aynı isimde bir hesap zaten var.',
            'seller_id.max' => 'Satıcı ID en fazla 160 karakter olabilir.',
            'api_key.required' => 'API anahtarı zorunludur.',
            'api_key.max' => 'API anahtarı en fazla 500 karakter olabilir.',
            'api_secret.required' => 'API gizli anahtarı zorunludur.',
            'api_secret.max' => 'API gizli anahtarı en fazla 500 karakter olabilir.',
        ]);

        MarketplaceAccount::query()->create([
            'tenant_id' => $tenantId,
            'marketplace_id' => $data['marketplace_id'],
            'name' => $data['name'],
            'status' => MarketplaceAccountStatus::Draft,
            'credentials' => [
                'seller_id' => $data['seller_id'] ?? null,
                'api_key' => $data['api_key'],
                'api_secret' => $data['api_secret'],
            ],
        ]);

        return redirect()->route('marketplace-accounts.index');
    }

    /**
     * @return list<array{id: int, code: string, name: string}>
     */
    private function activeMarketplaces(): array
    {
        return Marketplace::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Marketplace $marketplace): array => [
                'id' => $marketplace->id,
                'code' => $marketplace->code->value,
                'name' => $marketplace->name,
            ])
            ->all();
    }

    private function tenantId(Request $request): int
    {
        $tenantId = $request->user()?->tenant_id;

        abort_unless($tenantId, 403);

        return $tenantId;
    }
}
