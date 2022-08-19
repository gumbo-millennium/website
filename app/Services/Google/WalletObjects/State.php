<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum State: string
{
    case STATE_UNSPECIFIED = 'STATE_UNSPECIFIED';
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case EXPIRED = 'EXPIRED';
    case INACTIVE = 'INACTIVE';
}
