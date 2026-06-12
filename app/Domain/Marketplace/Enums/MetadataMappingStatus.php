<?php

namespace App\Domain\Marketplace\Enums;

enum MetadataMappingStatus: string
{
    case Mapped = 'mapped';
    case Pending = 'pending';
    case Ignored = 'ignored';
}
