<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Http\Requests\FileRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Changes to an existing file
 */
class FileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'optional|string',
            'public' => 'optional|boolean'
        ];
    }
}
