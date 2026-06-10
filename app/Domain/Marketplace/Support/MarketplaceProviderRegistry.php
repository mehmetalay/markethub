<?php

namespace App\Domain\Marketplace\Support;

use App\Domain\Marketplace\Contracts\MarketplaceProvider;
use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderNotFoundException;

class MarketplaceProviderRegistry
{
    /**
     * @var array<string, MarketplaceProvider>
     */
    private array $providers = [];

    public static function withDefaultProviders(): self
    {
        $registry = new self;

        foreach (MarketplaceCode::cases() as $code) {
            $registry->register(new ConfiguredMarketplaceProvider(
                code: $code,
                name: self::displayName($code),
                capabilities: [
                    MarketplaceCapability::ConnectionCheck,
                ],
            ));
        }

        return $registry;
    }

    public function register(MarketplaceProvider $provider): void
    {
        $this->providers[$provider->code()->value] = $provider;
    }

    public function has(MarketplaceCode $code): bool
    {
        return array_key_exists($code->value, $this->providers);
    }

    public function get(MarketplaceCode $code): MarketplaceProvider
    {
        return $this->providers[$code->value] ?? throw new MarketplaceProviderNotFoundException($code);
    }

    /**
     * @return array<string, MarketplaceProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    private static function displayName(MarketplaceCode $code): string
    {
        return match ($code) {
            MarketplaceCode::Trendyol => 'Trendyol',
            MarketplaceCode::TrendyolGo => 'Trendyol Go',
            MarketplaceCode::Hepsiburada => 'Hepsiburada',
            MarketplaceCode::N11 => 'n11',
            MarketplaceCode::Amazon => 'Amazon',
        };
    }
}
