<?php

declare(strict_types=1);

namespace App\Enums;

enum PhotoVisibility: int
{
    case Hidden = 0;
    case Visible = 1;
    case Pending = 2;
}
