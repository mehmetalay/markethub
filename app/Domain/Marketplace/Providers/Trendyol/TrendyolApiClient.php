<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use Illuminate\Http\Client\Response;

class TrendyolApiClient
{
    private const BASE_URL = 'https://apigw.trendyol.com';

    public function __construct(
        private readonly MarketplaceHttpClient $httpClient,
        private readonly TrendyolCredentials $credentials,
        private readonly TrendyolRateLimitGuard $rateLimitGuard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        return $this->get('/integration/product/product-categories');
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryAttributes(string $categoryId): array
    {
        return $this->get("/integration/product/product-categories/{$categoryId}/attributes");
    }

    /**
     * @return array<string, mixed>
     */
    public function brands(int $page): array
    {
        return $this->get('/integration/product/brands', [
            'page' => $page,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $this->rateLimitGuard->pause();

        $response = $this->httpClient
            ->pendingRequest()
            ->withBasicAuth($this->credentials->apiKey, $this->credentials->apiSecret)
            ->withHeaders([
                'User-Agent' => "{$this->credentials->supplierId} - MarketHub",
            ])
            ->timeout(30)
            ->get(self::BASE_URL.$path, $query);

        $this->assertSuccessful($response);

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    private function assertSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw new TrendyolProviderRequestException(
            statusCode: $response->status(),
            message: $this->safeErrorMessage($response),
        );
    }

    private function safeErrorMessage(Response $response): string
    {
        $payload = $response->json();
        $message = null;

        if (is_array($payload)) {
            $candidate = $payload['message'] ?? $payload['error'] ?? $payload['errors'] ?? null;
            $message = is_array($candidate) ? json_encode($candidate, JSON_THROW_ON_ERROR) : $candidate;
        }

        if (! is_string($message) || trim($message) === '') {
            $message = 'Trendyol metadata isteği başarısız oldu.';
        }

        return "Trendyol metadata isteği HTTP {$response->status()} koduyla başarısız oldu: {$message}";
    }
}
