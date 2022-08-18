<?php

declare(strict_types=1);

namespace App\Services\Google;

abstract class SmartModel extends \Google\Model
{
    /**
     * @var string[]
     */
    protected array $casts = [];

    /**
     * @var string[]
     */
    protected array $enums = [];

    /**
     * Converts the value to a class instance.
     * @param string|string[] $cast
     */
    private function getUsingCast(string|array $cast, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (is_array($cast)) {
            $cast = $cast[0];

            return array_map(fn ($value) => new $cast($value), $value);
        }

        return new $cast($value);
    }

    /**
     * Converts the value to an enum.
     */
    private function getUsingEnum(string $enum, mixed $value): mixed
    {
        if ($value) {
            return $enum::tryFrom($value) ?? null;
        }

        return $value;
    }

    public function __get($key)
    {
        if (! isset($this->processed[$key])) {
            if (isset($this->casts[$key])) {
                $this->processed[$key] = true;

                return $this->modelData[$key] = $this->getUsingCast($this->casts[$key], $this->modelData[$key]);
            }

            if (isset($this->enums[$key])) {
                $this->processed[$key] = true;

                return $this->modelData[$key] = $this->getUsingEnum($this->enums[$key], $this->modelData[$key]);
            }
        }

        return parent::__get($key);
    }
}
