<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Rules\PhoneNumber;

/**
 * A request with sign up data
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Get a date sixteen years ago
        $sixteenYears = today()->subYear(16)->format('Y-m-d');

        return [
            // We always need an e-mail address and would like a phone number
            'email' => 'required|email',
            'phone' => ['required', 'string', new PhoneNumber('NL')],

            // Also, we need them to accept our data policy
            'accept_policy' => 'required|accepted',

            // Names
            'first_name' => "required_without:name|string|min:2",
            'insertion' => 'sometimes|nullable|string|min:2',
            'last_name' => "required_without:name|string|min:2",

            // Address
            'street' => 'required|string|regex:/\w+/',
            'number' => 'required|string|regex:/^\d+/',
            'postal_code' => ['required', 'string', 'regex:/^([0-9A-Z \.]+)$/i'],
            'city' => 'required|string|min:2',

            // Personal data
            'date_of_birth' => [
                'required',
                'date_format:d-m-Y',
                "before:{$sixteenYears}"
            ],
            'gender' => [
                'required',
                Rule::in(['man', 'vrouw']),
            ],

            // Member type
            'windesheim_student' => 'sometimes|accepted',

            // Sign up for newsletter?
            'newsletter' => 'sometimes|boolean',
        ];
    }

    /**
     * Returns the data without sensitive fields
     *
     * @return array
     */
    public function safe() : array
    {
        return $this->except(['password', 'password_confirm']);
    }
}
