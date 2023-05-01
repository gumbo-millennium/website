<?php

declare(strict_types=1);

namespace App\Enums\Models;

use App\Helpers\Str;

/**
 * Barcode types used by the tickets.
 * The default is a QR code, of course, but we also support other types.
 * The type value should be a BarcodeType as defined in Google Wallet.
 * @see https://developers.google.com/wallet/tickets/events/rest/v1/BarcodeType
 */
enum BarcodeType: string
{
    public const DEFAULT = self::QRCODE;

    public const FALLBACK = self::TEXT;

    public static function fromString(?string $value): self
    {
        $lowercaseValue = (string) Str::of($value)->trim()->lower();
        $allCapsValue = strtoupper($lowercaseValue);

        if (! $allCapsValue) {
            return self::DEFAULT;
        }

        $constantName = self::class . '::' . $allCapsValue;

        return self::tryFrom($value) ?? defined($constantName) ? constant($constantName) : self::FALLBACK;
    }

    case CODABAR = 'codabar';
    case CODE39 = 'code39';
    case CODE128 = 'code128';
    case EAN8 = 'ean8';
    case EAN13 = 'ean13';
    case QRCODE = 'qrcode';
    case TEXT = 'text';
}
