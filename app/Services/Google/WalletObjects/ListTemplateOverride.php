<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class ListTemplateOverride extends \Google\Model
{
    /**
     * @var FirstRowOption
     */
    public $firstRowOption;

    /**
     * @var FieldSelector
     */
    public $secondRowOption;

    /**
     * @var FieldSelector
     */
    public $thirdRowOption;
}
