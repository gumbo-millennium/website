<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TimeInterval extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var DateTime
     */
    public $start;

    /**
     * @var DateTime
     */
    public $end;
}
