<?php

declare(strict_types=1);

namespace App\Enums;

enum PhotoReportResolution: string
{
    case Pending = 'pending';
    case Removed = 'removed';
    case Dismissed = 'dismissed';
}
