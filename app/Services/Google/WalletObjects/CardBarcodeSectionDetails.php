<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class CardBarcodeSectionDetails extends \Google\Model
{
    /**
     * @var BarcodeSectionDetail
     */
    public $firstTopDetail;

    /**
     * @var BarcodeSectionDetail
     */
    public $firstBottomDetail;

    /**
     * @var BarcodeSectionDetail
     */
    public $secondTopDetail;
}
