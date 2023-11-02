<?php

declare(strict_types=1);

namespace App\Events\Images;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static static dispatch(Model $model, string $attribute, string $attributeValue)
 * @method static static dispatchIf($boolean, Model $model, string $attribute, string $attributeValue)
 * @method static static dispatchUnless($boolean, Model $model, string $attribute, string $attributeValue)
 */
class ImageDeleted extends ImageEvent
{
    public function __construct(Model $model, string $attribute, private string $attributeValue)
    {
        parent::__construct($model, $attribute);
    }

    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }
}
