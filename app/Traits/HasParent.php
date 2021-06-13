<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\FileCategory;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Element has a parent.
 */
trait HasParent
{
    /**
     * Returns te.
     */
    public function parent(): Relation
    {
        return $this->belongsTo(FileCategory::class, 'id', 'parent_id');
    }
}
