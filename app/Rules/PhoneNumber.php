<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * Validates phone numbers using libphonenumber
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PhoneNumber implements Rule
{
    /**
     * The region to validate the number for
     *
     * @var string|null
     */
    private $region;

    /**
     * The current validator instance
     *
     * @var PhoneNumberUtil
     */
    private $util;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $region = null)
    {
        $this->region = $region;
        $this->util = PhoneNumberUtil::getInstance();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value)
    {
        // Get phonenumber
        $phoneNumber = null;

        // Try to parse the phone number
        try {
            $phoneNumber = $this->util->parse($value, $this->region);
        } catch (NumberParseException $e) {
            return false;
        }

        // Validate using region, if it's set
        if ($this->region !== null) {
            return $this->util->isValidNumberForRegion($phoneNumber, $this->region);
        }

        // Just validate the number otherwise
        return $this->util->isValidNumber($phoneNumber);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute phone number is not valid.';
    }
}
