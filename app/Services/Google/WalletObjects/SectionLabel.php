<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum SectionLabel: string
{
    case SECTION_LABEL_UNSPECIFIED = 'SECTION_LABEL_UNSPECIFIED';
    case SECTION = 'SECTION';
    case THEATER = 'THEATER';
}
