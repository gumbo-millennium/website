<?php

declare(strict_types=1);

namespace App\Models;

class Grant
{
    public string $key;

    public string $name;

    public string $description;

    public function __construct(
        string $key,
        string $name,
        string $description
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->description = $description;
    }

    public static function __set_state($properties)
    {
        return new self(
            $properties['key'],
            $properties['name'],
            $properties['description'],
        );
    }
}
