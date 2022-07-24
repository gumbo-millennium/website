<?php

declare(strict_types=1);

namespace App\Traits;

use App\Helpers\Str;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use JsonException;

trait DecryptsOldValues
{
    public function decryptValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (Str::contains($value, "\x17\x04")) {
            try {
                return Crypt::decrypt(Str::afterLast($value, "\x17\x04"), true);
            } catch (DecryptException $exception) {
                //
            }
        }

        try {
            return Crypt::decrypt($value, false);
        } catch (DecryptException $exception) {
            //
        }

        throw new InvalidArgumentException('Failed to decrypt value, tried two methods.');
    }

    public function decryptJsonValue($value)
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_decode($this->decryptValue($value), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Failed to decrypt value from JSON');
        }
    }
}
