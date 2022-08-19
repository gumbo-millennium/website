<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class RotatingBarcode extends \Google\Model
{
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
    public $valuePattern;

    /**
     * @var TotpDetails
     */
    public $totpDetails;

    /**
     * @var string
     */
    public $alternateText;

    /**
     * @var LocalizedString
     */
    public $showCodeText;
}
