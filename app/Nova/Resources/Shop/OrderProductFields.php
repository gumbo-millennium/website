<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Nova\Fields\Price;
use Laravel\Nova\Fields\Number;

/**
 * Fields that are present on the Order <-> ProductVariant.
 */
class OrderProductFields
{
    public function __invoke()
    {
        return [
            Number::make(__('Quantity'), 'quantity')
                ->onlyOnDetail(),
            Price::make(__('Total price'), fn ($orderItem) => ($orderItem->price * $orderItem->quantity) / 100)
                ->onlyOnDetail(),
        ];
    }
}
