<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Enums;

enum ConscriboErrorCodes: int
{
    public static function fromNotification(string $notification): ConscriboErrorCodes
    {
        return match ($notification) {
            'De gebruikersnaam wachtwoord combinatie is niet herkend. Probeer opnieuw.' => ConscriboErrorCodes::AuthInvalid,
            'Not authenticated.' => ConscriboErrorCodes::AuthExpired,
            default => ConscriboErrorCodes::Unknown,
        };
    }

    case Unknown = 0;
    case AuthInvalid = 101;
    case AuthExpired = 102;
    case HttpError = 201;
}
