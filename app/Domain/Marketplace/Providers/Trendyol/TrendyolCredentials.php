<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

use App\Domain\Marketplace\Exceptions\MarketplaceProviderCredentialException;
use App\Domain\Marketplace\Models\MarketplaceAccount;

final readonly class TrendyolCredentials
{
    public function __construct(
        public string $supplierId,
        public string $apiKey,
        public string $apiSecret,
    ) {}

    public static function fromAccount(MarketplaceAccount $account): self
    {
        $credentials = $account->credentials ?? [];
        $supplierId = self::credential($credentials, 'supplier_id');
        $apiKey = self::credential($credentials, 'api_key');
        $apiSecret = self::credential($credentials, 'api_secret');

        if ($supplierId === null || $apiKey === null || $apiSecret === null) {
            throw new MarketplaceProviderCredentialException('Trendyol API bilgileri eksik. supplier_id, api_key ve api_secret alanları tanımlanmalıdır.');
        }

        return new self($supplierId, $apiKey, $apiSecret);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private static function credential(array $credentials, string $key): ?string
    {
        $value = $credentials[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
