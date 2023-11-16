<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Brick\Money\Money;
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

    /**
     * Convert the value to a Money instance.
     * Extends the Nova handler to support Money objects being thrown in.
     */
    public function toMoneyInstance($value, $currency = null)
    {
        if ($value instanceof Money) {
            return $value;
        }

        return parent::toMoneyInstance($value, $currency);
    }
}
