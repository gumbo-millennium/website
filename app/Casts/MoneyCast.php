<?php

declare(strict_types=1);

namespace App\Casts;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    private string $currency;

    public function __construct(
        ?string $currency = null,
    ) {
        $this->currency = $currency ?? 'EUR';
    }

    /**
     * Cast the given value.
     *
     * @param null|int $value
     */
    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) && ! preg_match('/^\d+$/', $value)) {
            throw new InvalidArgumentException("Invalid value for money cast: {$value}");
        }

        return Money::ofMinor($value, $this->currency);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param null|float|int|Money $value
     */
    public function set($model, string $key, $value, array $attributes): ?int
    {
        // Ints and null are good to go
        if ($value === null || is_int($value)) {
            return null;
        }

        // Convert money to int
        if ($value instanceof Money) {
            return $value->getMinorAmount()->toScale(0, RoundingMode::UP)->toInt();
        }

        // Cast floats to a Money object and then to int
        if (is_float($value)) {
            return Money::ofMinor($value, $this->currency)->getMinorAmount()->toScale(0, RoundingMode::UP)->toInt();
        }

        // Invalid values deserve a fit
        throw new InvalidArgumentException("Invalid value for money cast: {$value}");
    }
}
