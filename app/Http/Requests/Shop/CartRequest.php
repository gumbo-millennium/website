<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

abstract class CartRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();
}
