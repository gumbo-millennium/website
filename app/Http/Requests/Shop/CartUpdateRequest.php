<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
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
                "between:0,{$this->getMaxQuantity()}",
            ],
        ];
    }
}
