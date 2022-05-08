<?php

declare(strict_types=1);

namespace App\Enums;

enum AlbumVisibility: string
{
    case Public = 'public';
    case ActivityOnly = 'activity-only';
    case Private = 'private';
}
