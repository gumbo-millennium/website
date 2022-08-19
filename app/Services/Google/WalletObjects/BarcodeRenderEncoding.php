<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum BarcodeRenderEncoding: string
{
    case RENDER_ENCODING_UNSPECIFIED = 'RENDER_ENCODING_UNSPECIFIED';
    case UTF_8 = 'UTF_8';
}
