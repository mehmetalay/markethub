<?php

namespace App\Domain\Marketplace\Enums;

enum MarketplaceAccountStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
}
