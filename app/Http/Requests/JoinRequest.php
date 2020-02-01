<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\JoinSubmission;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * A request with sign up data
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinRequest extends FormRequest
{
    /**
     * Phone rule
     *
     * @var PhoneNumber
     */
    private $phoneNumberRule;

    /**
     * Returns the phone rule
     *
     * @return PhoneNumber
     */
    private function phoneRule(): PhoneNumber
    {
        return $this->phoneNumberRule ?? ($this->phoneNumberRule = new PhoneNumber('NL'));
    }

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
            // Names
            'first_name' => "required|string|min:2",
            'insertion' => 'sometimes|nullable|string|min:2',
            'last_name' => "required|string|min:2",

            // Contact info
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', $this->phoneRule()],

            // Address
            'street' => ['required', 'string', 'regex:/\w+/'],
            'number' => ['required', 'string', 'regex:/^\d+/'],
            'postal_code' => ['required', 'string', 'regex:/^([0-9A-Z \.]+)$/i'],
            'city' => ['required', 'string', 'min:2'],

            // Personal data
            'date_of_birth' => "required|date_format:d-m-Y|before:{$sixteenYears}",
            'gender' => 'required|in:man,vrouw',

            // Boolean values
            'windesheim_student' => 'sometimes|boolean',
            'newsletter' => 'sometimes|boolean',
            'accept_policy' => 'required|accepted',
        ];
    }

    /**
     * Returns the data without sensitive fields
     *
     * @return array
     */
    public function safe(): array
    {
        return $this->except(['password', 'password_confirm']);
    }

    /**
     * Returns the submission for this data
     *
     * @return JoinSubmission
     */
    public function submission(): JoinSubmission
    {
        // get required data
        $submissionData = $this->only([
            'first_name',
            'insert',
            'last_name',
            'phone',
            'email',
            'date_of_birth',
            'gender',
            'street',
            'number',
            'city',
            'postal_code',
            'country',
            'windesheim_student',
            'newsletter'
        ])->toArray();

        // Format phone number, if possible
        $submissionData['phone'] = $this->phoneRule()->format($this->phone) ?? $this->phone;

        // Format email
        $submissionData['email'] = Str::lower($submissionData['email']);

        // Make submission
        return new JoinSubmission($submissionData);
    }
}
