<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum SecurityAnimation: string
{
    case ANIMATION_UNSPECIFIED = 'ANIMATION_UNSPECIFIED';
    case FOIL_SHIMMER = 'FOIL_SHIMMER';
}
