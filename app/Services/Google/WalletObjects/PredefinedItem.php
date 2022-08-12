<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum PredefinedItem: string
{
    case PREDEFINED_ITEM_UNSPECIFIED = 'PREDEFINED_ITEM_UNSPECIFIED';
    case FREQUENT_FLYER_PROGRAM_NAME_AND_NUMBER = 'FREQUENT_FLYER_PROGRAM_NAME_AND_NUMBER';
    case FLIGHT_NUMBER_AND_OPERATING_FLIGHT_NUMBER = 'FLIGHT_NUMBER_AND_OPERATING_FLIGHT_NUMBER';
}
