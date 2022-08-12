<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TotpDetails extends \Google\Model
{
    /**
     * @var string
     */
    public $periodMillis;

    /**
     * @var TotpAlgorithm
     */
    public $algorithm;

    /**
     * @var TotpParameters
     */
    public $parameters;
}
