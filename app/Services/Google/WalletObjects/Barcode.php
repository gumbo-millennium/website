<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Barcode extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var BarcodeType
     */
    public $type;

    /**
     * @var BarcodeRenderEncoding
     */
    public $renderEncoding;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $alternateText;

    /**
     * @var LocalizedString
     */
    public $showCodeText;
}
