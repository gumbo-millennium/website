<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

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
            'email' => 'required|email',

            // Names
            'first_name' => "required|string|min:2",
            'insertion' => 'nullable|string|min:2',
            'last_name' => "required|string|min:2",

            // Address
            'street' => 'required|string|regex:/\w+/',
            'number' => 'required|string|regex:/^\d+/',
            'postal_code' => ['required', 'string', 'regex:/^([0-9A-Z \.]+)$/i'],
            'city' => 'required|string|min:2',

            // Contact info
            'phone' => ['required', 'string', 'regex:/^\+?([\s\-\.]?\d){8,20}$/'],

            // Personal data
            'date_of_birth' => [
                'required',
                'date_format:d-m-Y',
                "before:{$sixteenYears}"
            ],
            'gender' => [
                'required',
                'min:2'
            ],

            // Member type
            'windesheim_student' => 'sometimes|accepted',

            // Policy acceptance
            'accept_policy' => 'required|accepted',
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
