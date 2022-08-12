<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum TransitOption: string
{
    case TRANSIT_OPTION_UNSPECIFIED = 'TRANSIT_OPTION_UNSPECIFIED';
    case ORIGIN_AND_DESTINATION_NAMES = 'ORIGIN_AND_DESTINATION_NAMES';
    case ORIGIN_AND_DESTINATION_CODES = 'ORIGIN_AND_DESTINATION_CODES';
    case ORIGIN_NAME = 'ORIGIN_NAME';
}
