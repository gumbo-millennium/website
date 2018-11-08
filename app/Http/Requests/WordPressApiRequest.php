<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Handles validation of a WordPress request
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class WordPressApiRequest extends FormRequest
{
    /**
     * Header used for authentication
     *
     * @var string
     */
    const AUTH_HEADER = 'X-WordPress-Auth';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        // Check for header
        if (!$request->headers->has(self::AUTH_HEADER)) {
            return false;
        }

        // Get header value
        $headerValue = $request->headers->get(self::AUTH_HEADER);

        // Check if value is non-empty and is equal to the auth token
        return (!empty($headerValue) && $headerValue === Option::get('gumbo-auth-token'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
