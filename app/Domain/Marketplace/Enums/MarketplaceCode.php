<?php

namespace App\Domain\Marketplace\Enums;

enum MarketplaceCode: string
{
    case Trendyol = 'trendyol';
    case TrendyolGo = 'trendyol_go';
    case Hepsiburada = 'hepsiburada';
    case N11 = 'n11';
    case Amazon = 'amazon';
}
