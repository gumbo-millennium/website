<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Rules\PhoneNumber;

/**
 * A request with minimal data for a user to sign up
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ShortJoinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can sign up
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Get a simple set of rules
        return [
            // We always need an e-mail address
            'email' => 'required|email',

            // We also need a phone number
            'phone' => ['nullable', 'string', new PhoneNumber('NL')],

            // And a name might be useful too
            'name' => 'required_without_all:first_name,insertion,last_name|string|min:4',

            // Lastly, we need them to accept our data policy
            'accept_policy' => 'required|accepted',
        ];
    }
}
