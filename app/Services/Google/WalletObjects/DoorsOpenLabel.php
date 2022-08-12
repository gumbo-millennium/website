<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum DoorsOpenLabel: string
{
    case DOORS_OPEN_LABEL_UNSPECIFIED = 'DOORS_OPEN_LABEL_UNSPECIFIED';
    case DOORS_OPEN = 'DOORS_OPEN';
    case GATES_OPEN = 'GATES_OPEN';
}
