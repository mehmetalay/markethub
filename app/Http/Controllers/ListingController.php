<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Listing\Enums\ListingPayloadType;
use App\Domain\Listing\Enums\ListingStatus;
use App\Domain\Listing\Enums\ListingVariantStatus;
use App\Domain\Listing\Models\Listing;
use App\Domain\Listing\Models\ListingPayload;
use App\Domain\Listing\Models\ListingStatusHistory;
use App\Domain\Listing\Models\ListingVariant;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('Listings/Index', [
            'listings' => Listing::query()
                ->with(['product', 'marketplaceAccount.marketplace'])
                ->where('tenant_id', $tenantId)
                ->latest()
                ->get()
                ->map(fn (Listing $listing): array => $this->listingListPayload($listing)),
        ]);
    }

    public function create(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        return Inertia::render('Listings/Create', $this->formOptions($tenantId));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);
        $data = $this->validatedListingData($request, $tenantId);

        $listing = DB::transaction(function () use ($data, $tenantId): Listing {
            $product = Product::query()
                ->with(['brand', 'category', 'variants.prices', 'variants.stock'])
                ->where('tenant_id', $tenantId)
                ->findOrFail($data['product_id']);

            $account = MarketplaceAccount::query()
                ->with('marketplace')
                ->where('tenant_id', $tenantId)
                ->findOrFail($data['marketplace_account_id']);

            $listing = Listing::query()->create([
                'tenant_id' => $tenantId,
                'marketplace_account_id' => $account->id,
                'marketplace_id' => $account->marketplace_id,
                'product_id' => $product->id,
                'title' => $product->name,
                'status' => ListingStatus::Draft,
            ]);

            foreach ($product->variants as $variant) {
                ListingVariant::query()->create([
                    'tenant_id' => $tenantId,
                    'listing_id' => $listing->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'status' => ListingVariantStatus::Draft,
                ]);
            }

            ListingPayload::query()->create([
                'tenant_id' => $tenantId,
                'listing_id' => $listing->id,
                'payload_type' => ListingPayloadType::Preview,
                'payload' => $this->previewPayload($product, $account),
                'generated_at' => now(),
            ]);

            ListingStatusHistory::query()->create([
                'tenant_id' => $tenantId,
                'listing_id' => $listing->id,
                'old_status' => null,
                'new_status' => ListingStatus::Draft,
                'message' => 'İlan taslağı oluşturuldu.',
                'metadata' => [
                    'source' => 'admin',
                ],
            ]);

            return $listing;
        });

        return redirect()->route('listings.show', $listing);
    }

    public function show(Request $request, int $listing): Response
    {
        $tenantId = $this->tenantId($request);
        $listingModel = Listing::query()
            ->with([
                'product',
                'marketplaceAccount.marketplace',
                'variants.productVariant',
                'payloads',
                'statusHistories',
                'errors.listingVariant',
            ])
            ->where('tenant_id', $tenantId)
            ->findOrFail($listing);

        return Inertia::render('Listings/Show', [
            'listing' => $this->listingDetailPayload($listingModel),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedListingData(Request $request, int $tenantId): array
    {
        return $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('tenant_id', $tenantId),
                Rule::unique('listings', 'product_id')
                    ->where('tenant_id', $tenantId)
                    ->where('marketplace_account_id', $request->integer('marketplace_account_id')),
            ],
            'marketplace_account_id' => [
                'required',
                'integer',
                Rule::exists('marketplace_accounts', 'id')->where('tenant_id', $tenantId),
            ],
        ], [
            'product_id.required' => 'Ürün seçimi zorunludur.',
            'product_id.exists' => 'Seçilen ürün bu çalışma alanına ait değil.',
            'product_id.unique' => 'Bu ürün için seçilen pazaryeri hesabında zaten ilan var.',
            'marketplace_account_id.required' => 'Pazaryeri hesabı seçimi zorunludur.',
            'marketplace_account_id.exists' => 'Seçilen pazaryeri hesabı bu çalışma alanına ait değil.',
        ]);
    }

    /**
     * @return array{products: list<array{id: int, name: string}>, marketplaceAccounts: list<array{id: int, name: string, marketplace: string}>}
     */
    private function formOptions(int $tenantId): array
    {
        return [
            'products' => Product::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                ])
                ->all(),
            'marketplaceAccounts' => MarketplaceAccount::query()
                ->with('marketplace')
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get()
                ->map(fn (MarketplaceAccount $account): array => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'marketplace' => $account->marketplace->name,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function listingListPayload(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'status' => $listing->status->value,
            'last_synced_at' => $listing->last_synced_at?->toISOString(),
            'updated_at' => $listing->updated_at?->toISOString(),
            'product' => [
                'id' => $listing->product->id,
                'name' => $listing->product->name,
            ],
            'marketplaceAccount' => [
                'id' => $listing->marketplaceAccount->id,
                'name' => $listing->marketplaceAccount->name,
                'marketplace' => $listing->marketplaceAccount->marketplace->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function listingDetailPayload(Listing $listing): array
    {
        return [
            ...$this->listingListPayload($listing),
            'external_id' => $listing->external_id,
            'variants' => $listing->variants
                ->map(fn (ListingVariant $variant): array => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'external_id' => $variant->external_id,
                    'status' => $variant->status->value,
                    'productVariant' => [
                        'id' => $variant->productVariant->id,
                        'name' => $variant->productVariant->name,
                        'sku' => $variant->productVariant->sku,
                    ],
                ])
                ->all(),
            'payloads' => $listing->payloads
                ->sortByDesc('created_at')
                ->map(fn (ListingPayload $payload): array => [
                    'id' => $payload->id,
                    'payload_type' => $payload->payload_type->value,
                    'payload' => $payload->payload,
                    'generated_at' => $payload->generated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'statusHistories' => $listing->statusHistories
                ->sortByDesc('created_at')
                ->map(fn (ListingStatusHistory $history): array => [
                    'id' => $history->id,
                    'old_status' => $history->old_status?->value,
                    'new_status' => $history->new_status->value,
                    'message' => $history->message,
                    'created_at' => $history->created_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'errors' => $listing->errors
                ->sortByDesc('created_at')
                ->map(fn ($error): array => [
                    'id' => $error->id,
                    'code' => $error->code,
                    'message' => $error->message,
                    'field' => $error->field,
                    'resolved_at' => $error->resolved_at?->toISOString(),
                    'variant_sku' => $error->listingVariant?->sku,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function previewPayload(Product $product, MarketplaceAccount $account): array
    {
        return [
            'type' => ListingPayloadType::Preview->value,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
            ],
            'marketplace_account' => [
                'id' => $account->id,
                'name' => $account->name,
                'marketplace' => [
                    'id' => $account->marketplace->id,
                    'code' => $account->marketplace->code->value,
                    'name' => $account->marketplace->name,
                ],
            ],
            'variants' => $product->variants
                ->map(fn ($variant): array => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'name' => $variant->name,
                    'price' => $variant->prices->first() ? [
                        'currency' => $variant->prices->first()->currency,
                        'sale_price' => $variant->prices->first()->sale_price,
                        'list_price' => $variant->prices->first()->list_price,
                    ] : null,
                    'stock' => $variant->stock ? [
                        'quantity' => $variant->stock->quantity,
                        'reserved_quantity' => $variant->stock->reserved_quantity,
                    ] : null,
                ])
                ->values()
                ->all(),
        ];
    }

    private function tenantId(Request $request): int
    {
        $tenantId = $request->user()?->tenant_id;

        abort_unless($tenantId, 403);

        return $tenantId;
    }
}
