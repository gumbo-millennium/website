<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class EventVenue extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var LocalizedString
     */
    public $name;

    /**
     * @var LocalizedString
     */
    public $address;
}
