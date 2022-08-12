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
}
