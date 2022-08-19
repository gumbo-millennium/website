<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Message extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $header;

    /**
     * @var string
     */
    public $body;

    /**
     * @var TimeInterval
     */
    public $displayInterval;

    /**
     * @var string
     */
    public $id;

    /**
     * @var MessageType|string
     */
    public $messageType;

    /**
     * @var LocalizedString
     */
    public $localizedHeader;

    /**
     * @var LocalizedString
     */
    public $localizedBody;
}
