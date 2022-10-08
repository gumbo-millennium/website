<?php

declare(strict_types=1);

namespace App\Enums\Models\GoogleWallet;

enum ReviewStatus: string
{
    case Unspecficied = '';
    case UnderReview = 'UNDER_REVIEW';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Draft = 'DRAFT';
}
