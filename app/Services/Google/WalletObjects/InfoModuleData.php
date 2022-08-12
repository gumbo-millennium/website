<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class InfoModuleData extends \Google\Model
{
    /**
     * @var LabelValueRow[]
     */
    public $labelValueRows;

    /**
     * @var bool
     */
    public $showLastUpdateTime;
}
