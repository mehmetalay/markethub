<?php

namespace App\Domain\Catalog\Enums;

enum ProductVariantStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
