<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class EventDateTime extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $doorsOpen;

    /**
     * @var string
     */
    public $start;

    /**
     * @var string
     */
    public $end;

    /**
     * @var DoorsOpenLabel|string
     */
    public $doorsOpenLabel;

    /**
     * @var LocalizedString
     */
    public $customDoorsOpenLabel;
}
