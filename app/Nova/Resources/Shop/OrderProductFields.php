<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Nova\Fields\Price;
use Laravel\Nova\Fields\Number;

/**
 * Fields that are present on the Order <-> ProductVariant
 */
class OrderProductFields
{
    public function __invoke()
    {
        return [
            Price::make(__('Price'), 'price')
                ->onlyOnDetail(),
            Number::make(__('VAT'), 'vat_rate')
                ->onlyOnDetail(),
        ];
    }
}
