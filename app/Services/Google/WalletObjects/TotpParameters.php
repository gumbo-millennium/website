<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TotpParameters extends \Google\Model
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var int
     */
    public $valueLength;
}
