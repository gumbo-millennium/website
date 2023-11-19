<?php

declare(strict_types=1);

namespace App\Enums\Models\Minisite;

enum PageType: string
{
    case Default = 'default';
    case Required = 'required';
}
