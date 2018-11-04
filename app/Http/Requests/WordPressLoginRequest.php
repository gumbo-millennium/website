<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WordPressLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        $user = $request->user();

        return $user && $user->hasAnyPermission([
            'content',
            'content-all',
            'content-admin'
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'scope' => [
                'required',
                'string',
                Rule::in(['user', 'admin'])
            ],
            'as' => [
                'required_if:scope,admin',
                'integer',
                'gt:0',
                'exists:wordpress.wp_users,ID'
            ]
        ];
    }
}
