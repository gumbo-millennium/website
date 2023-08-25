<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Models;

use Illuminate\Support\Arr;
use LogicException;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $type
 * @property-read int $parentId
 * @property-read Relation[] $members
 */
class Group
{
    protected array $members;

    public function __construct(array $attributes)
    {
        $this->attributes = Arr::except($attributes, 'members');

        $members = $attributes['members'] ?? [];

        foreach ($members as $key => $member) {
            if (is_array($member) && Arr::has($member, ['entityId', 'entityType'])) {
                $member = $members[$key] = new Relation($member['entityType'], ['code' => $member['entityId']]);
            }

            if (! $member instanceof Relation) {
                throw new LogicException('Members must contain items exclusively of type Relation.');
            }
        }
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
        return sprintf('%s %s (%d members)', $this->type, $this->name, count($this->members));
    }
}
