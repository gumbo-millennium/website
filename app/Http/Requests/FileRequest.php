<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\User;

/**
 * A file request, with slug and title
 */
class FileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(User $user)
    {
        return $user && $user->wp_user_level && $user->wp_user_level > 8;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => 'optional|string|unique:files.slug|max:60',
            'title' => 'optional|string|max:100',
            'public' => 'optional|boolean'
        ];
    }
}
