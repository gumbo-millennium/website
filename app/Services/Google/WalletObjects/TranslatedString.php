<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TranslatedString extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $value;

    public static function create(string $locale, string $text): self
    {
        if (empty($text)) {
            return self::NULL_VALUE;
        }

        return new self([
            'language' => $locale,
            'value' => $text,
        ]);
    }
}
