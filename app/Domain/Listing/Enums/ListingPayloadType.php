<?php

namespace App\Domain\Listing\Enums;

enum ListingPayloadType: string
{
    case Preview = 'preview';
    case PushRequest = 'push_request';
}
