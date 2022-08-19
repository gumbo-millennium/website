<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class AppLinkInfo extends \Google\Model
{
    /**
     * @var Image
     */
    public $appLogoImage;

    /**
     * @var LocalizedString
     */
    public $title;

    /**
     * @var LocalizedString
     */
    public $description;

    /**
     * @var AppTarget
     */
    public $appTarget;
}
