<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use App\Services\Conscribo\Enums\GroupType;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read GroupType $type
 * @property-read int $parentId
 * @property-read RelationIdentCollection $members
 */
class EntityGroup extends Fluent
{
    private const DEFAULT_VALUES = [
        'id' => '',
        'name' => '',
        'type' => GroupType::Universal,
        'parentId' => null,
        'members' => [],
    ];

    public function __construct(array $item = [])
    {
        if (is_array($item)) {
            $item = Arr::only(array_merge(self::DEFAULT_VALUES, $item), array_keys(self::DEFAULT_VALUES));
            $item['id'] = (int) $item['id'];
            $item['type'] = GroupType::tryFrom($item['type']) ?? GroupType::Universal;

            if ($item['parentId'] !== null) {
                $item['parentId'] = (int) $item['parentId'];
            }

            if (is_array($item['members'])) {
                $item['members'] = new RelationIdentCollection($item['members']);
            }
        }

        parent::__construct($item);
    }
}
