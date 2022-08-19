<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class TemplateItem extends \Google\Model
{
    /**
     * @var FieldSelector
     */
    public $firstValue;

    /**
     * @var FieldSelector
     */
    public $secondValue;

    /**
     * @var PredefinedItem|string
     */
    public $predefinedItem;
}
