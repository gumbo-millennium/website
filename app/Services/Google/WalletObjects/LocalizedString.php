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
}
