<?php

declare(strict_types=1);

namespace App\Enums\Models\GoogleWallet;

enum ObjectState: string
{
    case Unspecified = '';
    case Active = 'ACTIVE';
    case Completed = 'COMPLETED';
    case Expired = 'EXPIRED';
    case Inactive = 'INACTIVE';
}
