<?php

declare(strict_types=1);

namespace App\Http\Requests\Gallery\Filepond;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('upload', $this->album);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'image',
                'max:' . Config::get('gumbo.gallery.max_photo_size'),
            ],
        ];
    }
}
