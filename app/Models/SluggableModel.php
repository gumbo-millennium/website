<?php

declare(strict_types=1);

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that has a slug property, which is used to generate unique
 * looking URLs
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class SluggableModel extends Model
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
     * Return 'slug' as key name
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Returns a sluggable definition for this model
     *
     * @return array
     */
    abstract public function sluggable(): array;
}
