<?php

namespace App\Domain\Marketplace\Enums;

enum MetadataEntityType: string
{
    case Category = 'category';
    case Brand = 'brand';
    case Attribute = 'attribute';
    case AttributeValue = 'attribute_value';
}
