<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class FirstRowOption extends \Google\Model
{
    /**
     * @var string|TransitOption
     */
    public $transitOption;

    /**
     * @var FieldSelector
     */
    public $fieldOption;
}
