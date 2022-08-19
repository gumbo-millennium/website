<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum SeatLabel: string
{
    case SEAT_LABEL_UNSPECIFIED = 'SEAT_LABEL_UNSPECIFIED';
    case SEAT = 'SEAT';
}
