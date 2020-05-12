<?php

declare(strict_types=1);

namespace App\Rules;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Contracts\Validation\Rule;
use SplFileInfo;

/**
 * Validates if the given file is a valid SVG
 */
class ValidSvg implements Rule
{
    /**
     * Determine if the validation rule passes.
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function passes($attribute, $value)
    {
        // Skip if invalid
        if (!$value instanceof SplFileInfo) {
            return false;
        }

        // Start SVG validator
        $sanitizer = new Sanitizer();
        $sanitizer->removeRemoteReferences(true);

        // Clean contents
        $cleanContents = $sanitizer->sanitize(\file_get_contents($value->getPathname()));

        // Check if not-empty
        return !empty($cleanContents);
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message()
    {
        return 'Dit is geen geldige SVG afbeelding';
    }
}
