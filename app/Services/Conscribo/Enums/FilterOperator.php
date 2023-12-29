<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Enums;

enum FilterOperator: string
{
    public const STRING_OPERATOR = [
        self::Equals,
        self::Contains,
        self::DoesNotContain,
        self::StartsWith,
        self::NotEmpty,
        self::Empty,
    ];

    public const DATE_OPERATOR = [
        self::DateBetween,
        self::DateAfter,
        self::DateBefore,
    ];

    case Equals = '=';
    case Contains = '~';
    case DoesNotContain = '!~';
    case StartsWith = '|=';
    case NotEmpty = '+';
    case Empty = '-';
    case DateBetween = '><';
    case DateAfter = '>=';
    case DateBefore = '<=';
}
