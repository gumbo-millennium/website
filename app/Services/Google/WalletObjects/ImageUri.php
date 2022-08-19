<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class ImageUri extends \Google\Model
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     * @deprecated
     */
    public $description;

    /**
     * @var LocalizedString
     * @deprecated
     */
    public $localizedDescription;
}
