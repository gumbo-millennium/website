<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityExportType
{
    case CheckIn;
    case Medical;
    case Full;
}
