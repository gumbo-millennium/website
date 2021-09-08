<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

class CartAddRequest extends CartRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'variant' => [
                'required',
                'exists:shop_product_variants,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
