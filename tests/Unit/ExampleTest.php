<?php

namespace Tests\Unit;

use App\Domain\Marketplace\Enums\MarketplaceCapability;
use App\Domain\Marketplace\Enums\MarketplaceCode;
use App\Domain\Sync\Enums\SyncStatus;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_domain_enums_expose_stable_values(): void
    {
        $this->assertSame('order.read', MarketplaceCapability::OrderRead->value);
        $this->assertSame('trendyol_go', MarketplaceCode::TrendyolGo->value);
        $this->assertSame('running', SyncStatus::Running->value);
    }
}
