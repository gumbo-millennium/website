<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum MessageType: string
{
    case MESSAGE_TYPE_UNSPECIFIED = 'MESSAGE_TYPE_UNSPECIFIED';
    case TEXT = 'TEXT';
    case EXPIRATION_NOTIFICATION = 'EXPIRATION_NOTIFICATION';
}
