<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum DateFormat: string
{
    case DATE_TIME = 'DATE_TIME';
    case DATE_ONLY = 'DATE_ONLY';
    case TIME_ONLY = 'TIME_ONLY';
    case DATE_TIME_YEAR = 'DATE_TIME_YEAR';
    case DATE_YEAR = 'DATE_YEAR';
    case YEAR_MONTH = 'YEAR_MONTH';
    case YEAR_MONTH_DAY = 'YEAR_MONTH_DAY';
}
