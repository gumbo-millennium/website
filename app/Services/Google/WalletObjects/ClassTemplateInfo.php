<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class ClassTemplateInfo extends \Google\Model
{
    /**
     * @var CardBarcodeSectionDetails
     */
    public $cardBarcodeSectionDetails;

    /**
     * @var CardTemplateOverride
     */
    public $cardTemplateOverride;

    /**
     * @var DetailsTemplateOverride
     */
    public $detailsTemplateOverride;

    /**
     * @var ListTemplateOverride
     */
    public $listTemplateOverride;
}
