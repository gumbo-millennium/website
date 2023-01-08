<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Currency;

/**
 * A price field.
 */
class Price extends Currency
{
    /**
     * The symbol used by the currency.
     *
     * @var null|string
     */
    public $currency = 'EUR';

    /**
     * Whether the currency is using minor units.
     *
     * @var bool
     */
    public $minorUnits = true;
}
