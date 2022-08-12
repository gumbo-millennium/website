<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class EventReservationInfo extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $confirmationCode;
}
