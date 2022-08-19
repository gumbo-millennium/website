<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class CardRowThreeItems extends \Google\Model
{
    /**
     * @var TemplateItem
     */
    public $startItem;

    /**
     * @var TemplateItem
     */
    public $middleItem;

    /**
     * @var TemplateItem
     */
    public $endItem;
}
