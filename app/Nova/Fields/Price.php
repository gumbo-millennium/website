<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use InvalidArgumentException;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A price field.
 */
class Price extends Number
{
    /**
     * Format of the displayed value.
     *
     * @var string
     */
    protected $displayFormat = '€ %s';

    /**
     * Sets the string used to format the price, should be a printf-compatible format.
     */
    public function displayFormat(string $format): self
    {
        // Validate format
        if (! preg_match('/%(?!%)/', $format)) {
            throw new InvalidArgumentException("Format [${format}] does not seem to be a valid sprintf string");
        }

        $this->displayFormat = $format;

        return $this;
    }

    /**
     * Resolve the field's value for display.
     *
     * @param null|string $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        // Get value via parent
        parent::resolveForDisplay($resource, $attribute);

        // Format value
        if ($this->value !== null) {
            $this->value = sprintf($this->displayFormat, number_format($this->value, 2, ',', '.'));

            return;
        }

        // Return empty value
        $this->value = '–';
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        // Update value if present
        if (! $request->exists($requestAttribute)) {
            return;
        }

        $value = $request[$requestAttribute];

        $model->{$attribute} = $this->isNullValue($value) ? null : (float) $value * 100;
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param string $attribute
     */
    protected function resolveAttribute($resource, $attribute)
    {
        // Get value from parent
        $value = parent::resolveAttribute($resource, $attribute);

        // Divide by 100
        return $value !== null ? $value / 100 : null;
    }
}
