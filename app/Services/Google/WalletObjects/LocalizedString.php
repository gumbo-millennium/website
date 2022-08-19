<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class LocalizedString extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var TranslatedString[]
     */
    public $translatedValues;

    /**
     * @var TranslatedString
     */
    public $defaultValue;

    public static function create(string $locale, string $text): self|string
    {
        if (empty($text)) {
            return self::NULL_VALUE;
        }

        return new self([
            'defaultValue' => TranslatedString::create($locale, $text),
        ]);
    }
}
