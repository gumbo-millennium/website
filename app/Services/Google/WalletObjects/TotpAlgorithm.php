<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum TotpAlgorithm: string
{
    case TOTP_ALGORITHM_UNSPECIFIED = 'TOTP_ALGORITHM_UNSPECIFIED';
    case TOTP_SHA1 = 'TOTP_SHA1';
}
