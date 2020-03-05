<?php

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Helpers\Arr;
use App\Helpers\Str;

trait UseTemplateStrings
{
    private static $dummyNames = [
        ['John', null, 'Wick'],
        ['Willie', 'van', 'Oranje'],
        ['Robin', 'of', 'Loxley'],
        ['Knights', 'of', 'The Round Table'],
        ['John', null, 'Doe'],
    ];

    /**
     * Returns a placeholder name
     * @return array
     */
    protected function getTemplateName(): array
    {
        $name = Arr::random(self::$dummyNames);
        $name[3] = Str::slug(implode(' ', $name), '.') . '@example.com';
        return $name;
    }
}
