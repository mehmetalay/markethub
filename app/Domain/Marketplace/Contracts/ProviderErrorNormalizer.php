<?php

namespace App\Domain\Marketplace\Contracts;

use App\Domain\Marketplace\Data\ProviderError;
use Throwable;

interface ProviderErrorNormalizer
{
    public function normalize(Throwable $throwable): ProviderError;
}
