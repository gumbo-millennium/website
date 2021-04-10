<?php

declare(strict_types=1);

namespace App\Models;

class FormLayout
{
    public static function merge(
        self $existingLayout,
        ?string $name = null,
        ?string $type = null,
        array $options = []
    ): self {
        return new self(
            $name ?? $existingLayout->getName(),
            $type ?? $existingLayout->getType(),
            array_merge($existingLayout->getOptions(), $options ?? [])
        );
    }

    protected string $name;
    protected string $type;
    protected array $options;

    public function __construct(string $name, string $type, array $options)
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
