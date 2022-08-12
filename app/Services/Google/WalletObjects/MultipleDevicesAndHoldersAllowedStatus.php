<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum MultipleDevicesAndHoldersAllowedStatus: string
{
    case STATUS_UNSPECIFIED = 'STATUS_UNSPECIFIED';
    case MULTIPLE_HOLDERS = 'MULTIPLE_HOLDERS';
    case ONE_USER_ALL_DEVICES = 'ONE_USER_ALL_DEVICES';
    case ONE_USER_ONE_DEVICE = 'ONE_USER_ONE_DEVICE';
}
