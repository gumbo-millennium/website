<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class FieldReference extends \Google\Model
{
    /**
     * @var string
     */
    public $fieldPath;

    /**
     * @var DateFormat|string
     */
    public $dateFormat;
}
