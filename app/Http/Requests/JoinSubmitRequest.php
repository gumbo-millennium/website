<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;
use App\Rules\PhoneNumber;

class JoinSubmitRequest extends FormRequest
{
    /**
     * Users should be 16 years of age or older
     *
     * @return \DateTimeInterface
     */
    private function getBornBeforeDate() : \DateTimeInterface
    {
        return Carbon::today()->subYear(16);
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can send join requests
        return true;
    }

    /**
     * Customize errors on regexes
     *
     * @return array
     */
    public function messages()
    {
        return [
            'postal_code.regex' => 'Een geldige Nederlandse postcode is vereist',
            'date_of_birth.before' => sprintf(
                'Een geboortedatum voor %s is vereist.',
                $this->getBornBeforeDate()->format('d F Y')
            ),
        ];
    }

    /**
     * Sets the name of the fields
     *
     * @return array
     */
    public function attributes()
    {
        return [
            // Names
            'first_name' => 'voornaam',
            'insert' => 'tussenvoegsel',
            'last_name' => 'achternaam',

            // Contact info
            'email' => 'e-mail adres',
            'phone' => 'telefoonnummer',

            // Personal details
            'date_of_birth' => 'geboortedatum',
            'gender' => 'geslacht',

            // Address data
            'street' => 'straatnaam',
            'number' => 'huisnummer',
            'city' => 'stad',
            'postal_code' => 'postcode',
            'country' => 'land',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Names
            'first_name' => 'required|string',
            'insert' => 'nullable|string',
            'last_name' => 'required|string',

            // Contact info
            'email' => 'required|email',
            'phone' => [
                'required',
                new PhoneNumber('NL')
            ],

            // Personal details
            'date_of_birth' => [
                'required',
                'date_format:d-m-Y',
                sprintf('before:%s', $this->getBornBeforeDate()->format('Y-m-d')), // Users should be 16+
            ],
            'gender' => 'required|string',

            // Address data
            'street' => 'required|string|min:4',
            'number' => 'required|string|regex:/^\d+/',
            'city' => 'required|string|min:2',
            'postal_code' => 'required|string',
            'country' => 'required|string|regex:/^[A-Z]{2}$/',
        ];
    }

    /**
     * Adds conditional validation rules
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator &$validator) : void
    {
        // Adds a zipcode check if the country is The Netherlands
        $validator->sometimes('postal_code', 'required|regex:/^\d{4}[ ]?[a-z]{2}$/i', function ($input) {
            return $input->country === 'NL';
        });
    }
}
