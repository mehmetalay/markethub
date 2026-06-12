<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use App\Domain\Marketplace\Data\ProviderRequestContext;
use App\Domain\Marketplace\Enums\MetadataEntityType;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use App\Domain\Marketplace\Models\MarketplaceAccount;
use App\Domain\Marketplace\Models\MarketplaceAttribute;
use App\Domain\Marketplace\Models\MarketplaceAttributeValue;
use App\Domain\Marketplace\Models\MarketplaceBrand;
use App\Domain\Marketplace\Models\MarketplaceCategory;
use App\Domain\Marketplace\Models\MetadataSyncRun;
use App\Domain\Marketplace\Support\MarketplaceProviderErrorNormalizer;
use Illuminate\Contracts\Container\Container;
use Throwable;

class TrendyolMetadataSyncService
{
    public function __construct(
        private readonly Container $container,
        private readonly MarketplaceProviderErrorNormalizer $errorNormalizer,
        private readonly TrendyolRateLimitGuard $rateLimitGuard,
    ) {}

    public function syncCategories(MarketplaceAccount $account): MetadataSyncRun
    {
        $run = $this->startRun($account, MetadataEntityType::Category);

        try {
            $client = $this->apiClient($account);
            $categories = $this->flattenCategories($client->categories()['categories'] ?? []);
            $processed = 0;

            foreach ($categories as $category) {
                $marketplaceCategory = MarketplaceCategory::query()->updateOrCreate(
                    [
                        'marketplace_id' => $account->marketplace_id,
                        'external_id' => $category['external_id'],
                    ],
                    [
                        'parent_external_id' => $category['parent_external_id'],
                        'name' => $category['name'],
                        'path' => $category['path'],
                        'is_active' => true,
                        'raw_metadata' => $category['raw_metadata'],
                    ],
                );

                $this->recordItem($run, MetadataEntityType::Category, $marketplaceCategory->external_id, $marketplaceCategory->raw_metadata);
                $processed++;
            }

            return $this->succeedRun($run, $processed);
        } catch (Throwable $throwable) {
            return $this->failRun($run, $throwable);
        }
    }

    public function syncBrands(MarketplaceAccount $account): MetadataSyncRun
    {
        $run = $this->startRun($account, MetadataEntityType::Brand);

        try {
            $client = $this->apiClient($account);
            $page = 0;
            $processed = 0;

            do {
                $payload = $client->brands($page);
                $brands = $payload['brands'] ?? [];
                $totalPages = isset($payload['totalPages']) && is_numeric($payload['totalPages'])
                    ? (int) $payload['totalPages']
                    : null;

                foreach (is_array($brands) ? $brands : [] as $brand) {
                    if (! is_array($brand)) {
                        continue;
                    }

                    $externalId = $this->stringValue($brand['id'] ?? $brand['brandId'] ?? null);
                    $name = $this->stringValue($brand['name'] ?? null);

                    if ($externalId === null || $name === null) {
                        continue;
                    }

                    $marketplaceBrand = MarketplaceBrand::query()->updateOrCreate(
                        [
                            'marketplace_id' => $account->marketplace_id,
                            'external_id' => $externalId,
                        ],
                        [
                            'name' => $name,
                            'is_active' => true,
                            'raw_metadata' => $brand,
                        ],
                    );

                    $this->recordItem($run, MetadataEntityType::Brand, $marketplaceBrand->external_id, $marketplaceBrand->raw_metadata);
                    $processed++;
                }

                $page++;
            } while ($this->shouldContinueBrandPagination($brands, $page, $totalPages));

            return $this->succeedRun($run, $processed);
        } catch (Throwable $throwable) {
            return $this->failRun($run, $throwable);
        }
    }

    public function syncAttributes(MarketplaceAccount $account): MetadataSyncRun
    {
        $run = $this->startRun($account, MetadataEntityType::Attribute);

        try {
            $client = $this->apiClient($account);
            $processed = 0;

            $categories = MarketplaceCategory::query()
                ->where('marketplace_id', $account->marketplace_id)
                ->orderBy('id')
                ->get();

            foreach ($categories as $category) {
                $payload = $client->categoryAttributes($category->external_id);
                $attributes = $payload['categoryAttributes'] ?? $payload['attributes'] ?? [];

                foreach (is_array($attributes) ? $attributes : [] as $attributePayload) {
                    if (! is_array($attributePayload)) {
                        continue;
                    }

                    $attributeData = is_array($attributePayload['attribute'] ?? null)
                        ? $attributePayload['attribute']
                        : $attributePayload;

                    $externalId = $this->stringValue($attributeData['id'] ?? $attributeData['attributeId'] ?? null);
                    $name = $this->stringValue($attributeData['name'] ?? $attributeData['attributeName'] ?? null);

                    if ($externalId === null || $name === null) {
                        continue;
                    }

                    $marketplaceAttribute = MarketplaceAttribute::query()->updateOrCreate(
                        [
                            'marketplace_id' => $account->marketplace_id,
                            'external_id' => $externalId,
                        ],
                        [
                            'marketplace_category_id' => $category->id,
                            'name' => $name,
                            'code' => $this->stringValue($attributeData['code'] ?? null),
                            'type' => $this->stringValue($attributeData['type'] ?? null),
                            'is_required' => (bool) ($attributePayload['required'] ?? $attributePayload['isRequired'] ?? false),
                            'is_active' => true,
                            'raw_metadata' => $attributePayload,
                        ],
                    );

                    $this->recordItem($run, MetadataEntityType::Attribute, $marketplaceAttribute->external_id, $marketplaceAttribute->raw_metadata);
                    $processed++;

                    $values = $attributePayload['attributeValues'] ?? $attributePayload['values'] ?? [];
                    foreach (is_array($values) ? $values : [] as $valuePayload) {
                        if (! is_array($valuePayload)) {
                            continue;
                        }

                        $valueExternalId = $this->stringValue($valuePayload['id'] ?? $valuePayload['attributeValueId'] ?? null);
                        $value = $this->stringValue($valuePayload['name'] ?? $valuePayload['value'] ?? null);

                        if ($valueExternalId === null || $value === null) {
                            continue;
                        }

                        $marketplaceValue = MarketplaceAttributeValue::query()->updateOrCreate(
                            [
                                'marketplace_id' => $account->marketplace_id,
                                'marketplace_attribute_id' => $marketplaceAttribute->id,
                                'external_id' => $valueExternalId,
                            ],
                            [
                                'value' => $value,
                                'code' => $this->stringValue($valuePayload['code'] ?? null),
                                'raw_metadata' => $valuePayload,
                            ],
                        );

                        $this->recordItem($run, MetadataEntityType::AttributeValue, $marketplaceValue->external_id, $marketplaceValue->raw_metadata);
                    }
                }
            }

            return $this->succeedRun($run, $processed);
        } catch (Throwable $throwable) {
            return $this->failRun($run, $throwable);
        }
    }

    private function apiClient(MarketplaceAccount $account): TrendyolApiClient
    {
        $account->loadMissing('marketplace');
        $context = ProviderRequestContext::fromAccount($account);

        return new TrendyolApiClient(
            httpClient: $this->container->make(MarketplaceHttpClient::class, [
                'context' => $context,
            ]),
            credentials: TrendyolCredentials::fromAccount($account),
            rateLimitGuard: $this->rateLimitGuard,
        );
    }

    private function startRun(MarketplaceAccount $account, MetadataEntityType $entityType): MetadataSyncRun
    {
        $account->loadMissing('marketplace');

        return MetadataSyncRun::query()->create([
            'tenant_id' => $account->tenant_id,
            'marketplace_id' => $account->marketplace_id,
            'marketplace_account_id' => $account->id,
            'entity_type' => $entityType,
            'status' => MetadataSyncStatus::Running,
            'started_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function recordItem(MetadataSyncRun $run, MetadataEntityType $entityType, ?string $externalId, ?array $metadata): void
    {
        $run->items()->create([
            'tenant_id' => $run->tenant_id,
            'entity_type' => $entityType,
            'external_id' => $externalId,
            'status' => MetadataSyncStatus::Succeeded,
            'metadata' => $metadata,
        ]);
    }

    private function succeedRun(MetadataSyncRun $run, int $processed): MetadataSyncRun
    {
        $run->update([
            'status' => MetadataSyncStatus::Succeeded,
            'finished_at' => now(),
            'summary' => [
                'processed' => $processed,
            ],
        ]);

        return $run->refresh();
    }

    private function failRun(MetadataSyncRun $run, Throwable $throwable): MetadataSyncRun
    {
        $error = $this->errorNormalizer->normalize($throwable);

        $run->update([
            'status' => MetadataSyncStatus::Failed,
            'finished_at' => now(),
            'message' => $error->message,
            'summary' => [
                'error' => $error->toArray(),
            ],
        ]);

        $run->items()->create([
            'tenant_id' => $run->tenant_id,
            'entity_type' => $run->entity_type,
            'status' => MetadataSyncStatus::Failed,
            'message' => $error->message,
        ]);

        return $run->refresh();
    }

    /**
     * @return list<array{external_id: string, parent_external_id: string|null, name: string, path: string|null, raw_metadata: array<string, mixed>}>
     */
    private function flattenCategories(mixed $categories, ?string $parentId = null, array $path = []): array
    {
        if (! is_array($categories)) {
            return [];
        }

        $flattened = [];

        foreach ($categories as $category) {
            if (! is_array($category)) {
                continue;
            }

            $externalId = $this->stringValue($category['id'] ?? $category['categoryId'] ?? null);
            $name = $this->stringValue($category['name'] ?? $category['categoryName'] ?? null);

            if ($externalId === null || $name === null) {
                continue;
            }

            $categoryPath = [...$path, $name];
            $flattened[] = [
                'external_id' => $externalId,
                'parent_external_id' => $parentId ?? $this->stringValue($category['parentId'] ?? null),
                'name' => $name,
                'path' => implode(' / ', $categoryPath),
                'raw_metadata' => $category,
            ];

            $children = $category['subCategories'] ?? $category['children'] ?? [];
            $flattened = [
                ...$flattened,
                ...$this->flattenCategories($children, $externalId, $categoryPath),
            ];
        }

        return $flattened;
    }

    private function shouldContinueBrandPagination(mixed $brands, int $nextPage, ?int $totalPages): bool
    {
        if ($totalPages !== null) {
            return $nextPage < $totalPages;
        }

        return is_array($brands) && count($brands) > 0;
    }

    private function stringValue(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
