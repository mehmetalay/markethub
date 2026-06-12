<?php

namespace App\Domain\Catalog\Enums;

enum CatalogStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
