<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Exceptions;

class AuthenticationException extends ConscriboException
{
    public const CODE_INVALID_CREDENTIALS = 401;

    public const CODE_NEEDS_AUTHENTICATION = 402;

    private const INVALID_CREDENTIALS = 'De gebruikersnaam wachtwoord combinatie is niet herkend. Probeer opnieuw.';

    private const NEEDS_AUTHENTICATION = 'Not authenticated.';

    protected static function determineCodeFromNotification(string $notification): ?int
    {
        return match ($notification) {
            self::INVALID_CREDENTIALS => self::CODE_INVALID_CREDENTIALS,
            self::NEEDS_AUTHENTICATION => self::CODE_NEEDS_AUTHENTICATION,
            default => null,
        };
    }
}
