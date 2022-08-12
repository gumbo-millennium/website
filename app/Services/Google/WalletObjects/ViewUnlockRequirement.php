<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum ViewUnlockRequirement: string
{
    case VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED = 'VIEW_UNLOCK_REQUIREMENT_UNSPECIFIED';
    case UNLOCK_NOT_REQUIRED = 'UNLOCK_NOT_REQUIRED';
    case UNLOCK_REQUIRED_TO_VIEW = 'UNLOCK_REQUIRED_TO_VIEW';
}
