<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Models;

use LogicException;

class Relation
{
    protected array $attributes = [];

    protected string $type;

    public function __construct(string $type, array $attributes = [])
    {
        $this->type = $type;

        $this->attributes = $attributes;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function __set(string $name, $value): void
    {
        throw new LogicException('Cannot set attributes on a relation.');
    }

    public function __unset(string $name): void
    {
        throw new LogicException('Cannot unset attributes on a relation.');
    }

    public function __toString(): string
    {
        return sprintf('%s %s (%s)', $this->type, $this->code ?? $this->id, $this->name ?? $this->naam);
    }
}
