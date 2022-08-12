<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class LabelValue extends \Google\Model
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $value;

    /**
     * @var LocalizedString
     */
    public $localizedLabel;

    /**
     * @var LocalizedString
     */
    public $localizedValue;
}
