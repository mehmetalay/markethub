<?php

namespace App\Domain\Marketplace\Enums;

enum MarketplaceConnectionStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
}
