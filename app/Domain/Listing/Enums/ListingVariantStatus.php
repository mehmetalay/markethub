<?php

namespace App\Domain\Listing\Enums;

enum ListingVariantStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Pending = 'pending';
    case Published = 'published';
    case Failed = 'failed';
    case Archived = 'archived';
}
