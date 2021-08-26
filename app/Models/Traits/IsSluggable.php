<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

trait IsSluggable
{
    use Sluggable;
    use SluggableScopeHelpers;

    /**
     * Define the slug property, which is quicker than letting the
     * system search each time.
     *
     * @var string
     */
    protected $slugKeyName = 'slug';

    /**
     * Returns a sluggable definition for this model.
     */
    abstract public function sluggable(): array;

    /**
     * Return 'slug' as key name.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
