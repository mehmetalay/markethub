<?php

namespace App\Domain\Marketplace\Data;

use App\Domain\Marketplace\Enums\MarketplaceConnectionStatus;

final readonly class ProviderResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public MarketplaceConnectionStatus $status,
        public string $message,
        public array $metadata = [],
        public ?ProviderError $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function pending(string $message, array $metadata = []): self
    {
        return new self(MarketplaceConnectionStatus::Pending, $message, $metadata);
    }

    public static function failed(ProviderError $error): self
    {
        return new self(MarketplaceConnectionStatus::Failed, $error->message, [], $error);
    }

    /**
     * @return array{status: string, message: string, metadata: array<string, mixed>, error: array{code: string, message: string, category: string}|null}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'error' => $this->error?->toArray(),
        ];
    }
}
