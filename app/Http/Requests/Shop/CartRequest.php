<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Support\Facades\Config;

abstract class CartRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Max quantity per variant.
     */
    public function getMaxQuantity(): int
    {
        return Config::get('gumbo.shop.order-limit', 5);
    }
}
