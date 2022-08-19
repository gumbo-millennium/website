<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum ConfirmationCodeLabel: string
{
    case CONFIRMATION_CODE_LABEL_UNSPECIFIED = 'CONFIRMATION_CODE_LABEL_UNSPECIFIED';
    case CONFIRMATION_CODE = 'CONFIRMATION_CODE';
    case CONFIRMATION_NUMBER = 'CONFIRMATION_NUMBER';
    case ORDER_NUMBER = 'ORDER_NUMBER';
    case RESERVATION_NUMBER = 'RESERVATION_NUMBER';
}
