<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\FileCategory;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Element has a parent
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait HasParent
{
    /**
     * Returns te
     *
     * @return Relation
     */
    public function parent(): Relation
    {
        return $this->belongsTo(FileCategory::class, 'id', 'parent_id');
    }
}
