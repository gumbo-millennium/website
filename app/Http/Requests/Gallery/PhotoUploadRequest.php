<?php

declare(strict_types=1);

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

/**
 * @property \App\Models\Gallery\Album $album
 * @property \Illuminate\Http\UploadedFile $file
 */
class PhotoUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        $album = $this->album;

        return $user && $album && $user->can('upload', $album);
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
