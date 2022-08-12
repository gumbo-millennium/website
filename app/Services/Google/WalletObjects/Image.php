<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Image extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var ImageUri
     */
    public $sourceUri;

    /**
     * @var LocalizedString
     */
    public $contentDescription;
}
