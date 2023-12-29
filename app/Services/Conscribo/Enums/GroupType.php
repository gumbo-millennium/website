<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Enums;

enum GroupType: string
{
    case Universal = 'universal';
    case Archived = 'archived';
}
