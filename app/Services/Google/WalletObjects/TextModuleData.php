<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TextModuleData extends \Google\Model
{
    /**
     * @var string
     */
    public $header;

    /**
     * @var string
     */
    public $body;

    /**
     * @var LocalizedString
     */
    public $localizedHeader;

    /**
     * @var LocalizedString
     */
    public $localizedBody;

    /**
     * @var string
     */
    public $id;
}
