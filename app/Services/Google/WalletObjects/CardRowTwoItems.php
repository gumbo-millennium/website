<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class CardRowTwoItems extends \Google\Model
{
    /**
     * @var TemplateItem
     */
    public $startItem;

    /**
     * @var TemplateItem
     */
    public $endItem;
}
