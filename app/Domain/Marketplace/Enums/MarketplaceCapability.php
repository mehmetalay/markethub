<?php

namespace App\Domain\Marketplace\Enums;

enum MarketplaceCapability: string
{
    case CatalogRead = 'catalog.read';
    case ListingWrite = 'listing.write';
    case OrderRead = 'order.read';
    case ShipmentWrite = 'shipment.write';
    case InvoiceWrite = 'invoice.write';
    case ReturnRead = 'return.read';
}
