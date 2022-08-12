<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class CardRowTemplateInfo extends \Google\Model
{
    /**
     * @var CardRowOneItem
     */
    public $oneItem;

    /**
     * @var CardRowTwoItems
     */
    public $twoItems;

    /**
     * @var CardRowThreeItems
     */
    public $threeItems;
}
