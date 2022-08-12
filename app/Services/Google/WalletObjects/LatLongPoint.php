<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class LatLongPoint extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;
}
