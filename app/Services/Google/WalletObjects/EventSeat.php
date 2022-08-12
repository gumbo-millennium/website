<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class EventSeat extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var LocalizedString
     */
    public $seat;

    /**
     * @var LocalizedString
     */
    public $row;

    /**
     * @var LocalizedString
     */
    public $section;

    /**
     * @var LocalizedString
     */
    public $gate;
}
