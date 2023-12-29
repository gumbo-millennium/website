<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Enums;

enum FieldType: string
{
    private const TEXT_LIKE_FIELDS = [
        'text',
        'constant',
        'textarea',
        'enum',
        'mailadres',
    ];

    private const DATE_FIELDS = [
        'date',
        'Datetime',
    ];

    /**
     * Convert from API-response type to enum.
     */
    public static function fromApiType(string $type): FieldType
    {
        if (in_array($type, self::TEXT_LIKE_FIELDS, true)) {
            return self::Text;
        }

        if (in_array($type, self::DATE_FIELDS, true)) {
            return self::Date;
        }

        foreach (self::cases() as $case) {
            if ($case->value === $type) {
                return $case;
            }
        }

        return self::Unknown;
    }

    case Unknown = '';
    case Text = 'text';
    case Number = 'number';
    case Integer = 'integer';
    case Date = 'date';
    case Amount = 'amount';
    case Account = 'account';
    case Checkbox = 'checkbox';
}
