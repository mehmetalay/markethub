<?php

namespace App\Domain\Marketplace\Support;

use App\Domain\Marketplace\Contracts\MarketplaceProvider;
use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Marketplace\Exceptions\MarketplaceProviderCapabilityException;

final readonly class ConfiguredMarketplaceProvider implements MarketplaceProvider
{
    /**
     * @param  list<MarketplaceCapability>  $capabilities
     */
    public function __construct(
        private MarketplaceCode $code,
        private string $name,
        private array $capabilities,
    ) {}

    public function code(): MarketplaceCode
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function supports(MarketplaceCapability $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function ensureSupports(MarketplaceCapability $capability): void
    {
        if (! $this->supports($capability)) {
            throw new MarketplaceProviderCapabilityException($this->code, $capability);
        }
    }
}
