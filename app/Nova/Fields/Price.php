<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A price field
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Price extends Number
{
    /**
     * Format of the displayed value
     *
     * @var string
     */
    protected $displayFormat = '€ %s';

    /**
     * Sets the string used to format the price, should be a printf-compatible format.
     *
     * @param string $format
     * @return self
     */
    public function displayFormat(string $format) : self
    {
        // Validate format
        if (!preg_match('/%(?!%)/', $format)) {
            throw new \InvalidArgumentException("Format [$format] does not seem to be a valid sprintf string");
        }

        $this->displayFormat = $format;
        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return mixed
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        // Update value if present
        if ($request->exists($requestAttribute)) {
            $value = $request[$requestAttribute];

            $model->{$attribute} = $this->isNullValue($value) ? null : floatval($value) * 100;
        }
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return mixed
     */
    protected function resolveAttribute($resource, $attribute)
    {
        // Get value from parent
        $value = parent::resolveAttribute($resource, $attribute);

        // Divide by 100
        return ($value !== null) ? $value / 100 : null;
    }


    /**
     * Resolve the field's value for display.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
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
}
