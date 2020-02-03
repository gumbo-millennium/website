<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Validates phone numbers using libphonenumber
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PhoneNumber implements Rule
{
    /**
     * The region to validate the number for
     * @var string|null
     */
    private $region;

    /**
     * The current validator instance
     * @var PhoneNumberUtil
     */
    private $util;

    /**
     * Create a new rule instance.
     * @return void
     */
    public function __construct(?string $region = null)
    {
        $this->region = $region;
        $this->util = PhoneNumberUtil::getInstance();
    }

    /**
     * Determine if the validation rule passes.
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        // Get phonenumber
        $number = null;

        // Try to parse the phone number
        try {
            $number = $this->util->parse($value, $this->region);
        } catch (NumberParseException $e) {
            return false;
        }

        // Validate using region, if it's set
        if ($this->region !== null) {
            return $this->util->isValidNumberForRegion($number, $this->region);
        }

        // Just validate the number otherwise
        return $this->util->isValidNumber($number);
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message()
    {
        return 'The phone number is not valid.';
    }

    /**
     * Formats phone numbers to a standardised form
     * @param string $value Phone number to parse
     * @return string|null Returns null if parsing failed
     */
    public function format(string $value): ?string
    {
        // Get phone util
        $util = PhoneNumberUtil::getInstance();

        // Try to get the number
        try {
            $number = $util->parse($value, $this->region);

            // Format for international use
            return $util->format($number, PhoneNumberFormat::INTERNATIONAL);
        } catch (NumberParseException $e) {
            return false;
        }
    }
}
