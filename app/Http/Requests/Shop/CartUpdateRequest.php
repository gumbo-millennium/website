<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

class CartUpdateRequest extends CartRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'required',
                'string',
            ],
            'quantity' => [
                'required',
                'min:0',
            ],
        ];
    }
}
