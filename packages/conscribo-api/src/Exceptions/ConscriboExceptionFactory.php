<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Exceptions;

use Gumbo\ConscriboApi\Contracts\ConscriboException;
use Throwable;

final class ConscriboExceptionFactory
{
    public const CODE_UNKNOWN = 0;

    /**
     * Exception Types, all of type ConscriboException.
     *
     * @var ConscriboException[]
     */
    private const EXCEPTION_TYPES = [
        AuthenticationException::class,
    ];

    protected function __construct(string $message, int $code = 0, Throwable $previous = null, array $notifications = [])
    {
        parent::__construct($message, $code, $previous);

        $this->notifications = $notifications ?? [$message];
    }

    public static function buildFromNotification(array $notifications): ConscriboException
    {
        $firstNotification = head($notifications) ?? 'Unknown error.';

        foreach (self::EXCEPTION_TYPES as $type) {
            $code = $type::determineCodeFromNotification($firstNotification);
            if ($code !== null) {
                return new $type($firstNotification, $code, null, $notifications);
            }
        }

        return new GenericException($firstNotification, GenericException::CODE_UNKNOWN, null, $notifications);
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }

    abstract protected static function determineCodeFromNotification(string $notification): ?int;
}
