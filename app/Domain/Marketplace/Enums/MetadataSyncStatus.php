<?php

namespace App\Domain\Marketplace\Enums;

enum MetadataSyncStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
