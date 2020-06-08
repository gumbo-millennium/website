<?php

declare(strict_types=1);

namespace App\Casts;

use App\Casts\Helpers\Caster;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class EncryptedAttribute implements CastsAttributes
{
    /**
     * Shorthand to create type
     * @param null|string $type
     * @return string
     */
    public static function make(?string $type = null): string
    {
        return $type ? (static::class . ":{$type}") : static::class;
    }

    /**
     * The type for coercion.
     */
    protected string $type;

    /**
     * Caster that casts to and from the database
     */
    protected Caster $caster;

    /**
     * Create a new cast class instance.
     * @param string|null $type
     */
    public function __construct(?string $type = null)
    {
        $this->type = $type === 'null' ? null : $type;

        if ($this->type) {
            $this->caster = new Caster($type);
        }
    }

    /**
     * Cast the given value.
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function get($model, $key, $value, $attributes)
    {
        // Decrypt if set
        if ($value !== null) {
            $value = Crypt::decryptString($value);
        }

        // Return as-is if no type casting is to take place
        if (!$this->type) {
            return $value;
        }

        // Don't decrypt null values
        if ($value === null) {
            return null;
        }

        // Update caster to work properly
        $this->caster->setModel($model);

        // Cast from table
        return $this->caster->castFromDatabase($value);
    }

    /**
     * Prepare the given value for storage.
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function set($model, $key, $value, $attributes)
    {
        // Don't convert null values
        if ($value === null) {
            return null;
        }

        // Update caster
        $this->caster->setModel($model);

        // Convert to string value
        $value = $this->caster->castToDatabase($value);

        // Encrypt value
        return Crypt::encryptString($value);
    }
}
