<?php

declare(strict_types=1);

namespace App\Casts;

use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;

class EncryptedDateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function get($model, string $key, $value, array $attributes): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $value = Crypt::decrypt($value, false);

        return Date::parse($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param DateTimeInterface $value
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof DateTimeInterface) {
            $value = Date::parse($value);
        } elseif (! $value instanceof Carbon) {
            $value = Carbon::instance($value);
        }

        return Crypt::encrypt($value->toIso8601String(), false);
    }
}
