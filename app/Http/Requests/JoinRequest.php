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
        $user = $this->user();

        // Update Ignore rule if user is logged in
        $uniqueRule = Rule::unique('users', 'email');
        if ($user) {
            $uniqueRule = $uniqueRule->ignore($user->id);
        }

        // Change requiredness of fields depending on who's asking
        $joinOnly = $user ? 'required' : 'required_without:register_only';
        $requiredNewOnly = $user ? 'required' : 'optional';

        // Get a date sixteen years ago
        $sixteenYears = today()->subYear(16)->format('Y-m-d');

        return [
            // Allow any e-mail, as long as it doesn't exist OR equals the current user's e-mail address
            'email' => ['required', 'email', $uniqueRule],

            // Names
            'first_name' => "required|string|min:2",
            'insertion' => 'nullable|string|min:2',
            'last_name' => "required|string|min:2",

            // Address
            'street' => 'required|string|regex:/\w+/',
            'number' => 'required|string|regex:/^\d+/',
            'zipcode' => 'required|string|regex:/^[0-9A-Z \.]+$/',
            'city' => 'required|string|min:2',

            // Contact info
            'phone' => 'required|string|regex:/^\+?([\s-\.]?\d){8,}/',
            'date-of-birth' => [
                'required',
                'date_format:d-m-Y',
                "before:{$sixteenYears}"
            ],

            // Member type
            'windesheim-student' => 'sometimes|accepted',

            // Policy acceptance
            'accept-policy' => 'required|accepted',
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
