<?php

declare(strict_types=1);

namespace App\Enums;

enum BackupType: string
{
    case Full = 'full';
    case Incremental = 'incremental';
}
