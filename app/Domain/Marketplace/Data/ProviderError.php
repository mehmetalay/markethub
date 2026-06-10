<?php

namespace App\Domain\Marketplace\Data;

final readonly class ProviderError
{
    public function __construct(
        public string $code,
        public string $message,
        public string $category = 'provider',
    ) {}

    /**
     * @return array{code: string, message: string, category: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'category' => $this->category,
        ];
    }
}
