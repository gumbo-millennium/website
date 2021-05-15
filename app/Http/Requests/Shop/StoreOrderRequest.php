<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Http\Requests\Shop\StoreRequest;

class StoreOrderRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
