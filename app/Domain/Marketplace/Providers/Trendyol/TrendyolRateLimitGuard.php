<?php

namespace App\Domain\Marketplace\Providers\Trendyol;

class TrendyolRateLimitGuard
{
    public function __construct(
        private readonly int $delayMilliseconds = 0,
    ) {}

    public function pause(): void
    {
        if ($this->delayMilliseconds <= 0) {
            return;
        }

        usleep($this->delayMilliseconds * 1000);
    }
}
