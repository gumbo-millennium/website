<?php

declare(strict_types=1);

namespace App\Events\Images;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static static dispatch(Model $model, string $property, string $value)
 * @method static static dispatchIf($boolean, Model $model, string $property, string $value)
 * @method static static dispatchUnless($boolean, Model $model, string $property, string $value)
 */
abstract class ImageEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        private readonly Model $model,
        private string $attribute,
    ) {
        // no-op
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
