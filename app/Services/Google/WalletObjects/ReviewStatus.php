<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum ReviewStatus: string
{
    case REVIEW_STATUS_UNSPECIFIED = 'REVIEW_STATUS_UNSPECIFIED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case DRAFT = 'DRAFT';
}
