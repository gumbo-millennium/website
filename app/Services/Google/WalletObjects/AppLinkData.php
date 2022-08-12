<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class AppLinkData extends \Google\Model
{
    /**
     * @var AppLinkInfo
     */
    public $androidAppLinkInfo;

    /**
     * @var AppLinkInfo
     */
    public $iosAppLinkInfo;

    /**
     * @var AppLinkInfo
     */
    public $webAppLinkInfo;
}
