<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait IsUuidModel
{
    /**
     *  Setup model event hooks
     */
    protected static function bootIsUuidModel()
    {
        self::creating(static function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * Make sure model does not increment.
     *
     * @return void
     */
    protected function initializeIsUuidModel(): void
    {
        $this->incrementing = false;
        $this->keyType = "uuid";
    }
}
